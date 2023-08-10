<?php

declare(strict_types=1);
/*
 * Copyright (c) 2019, whatwedo GmbH
 * All rights reserved
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace whatwedo\CronBundle\Manager;

use Cron\CronExpression;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;
use whatwedo\CronBundle\CronJob\CronInterface;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Repository\ExecutionRepository;

class ExecutionManager
{
    public function __construct(
        protected LoggerInterface $logger,
        protected EntityManagerInterface $em,
        protected ExecutionRepository $repository,
        protected CronJobManager $cronJobManager,
        protected string $projectDir,
        protected string $environment
    ) {
    }

    public function check(): void
    {
        // Cleanup stale
        $this->cleanupStale();

        // Check all cron jobs
        foreach ($this->cronJobManager->getCronJobs() as $cronJob) {
            if ($this->isRunNeeded($cronJob)) {
                $this->schedule($cronJob);
            }
        }
    }

    /**
     * Return date of last execution or null if there is no previous run.
     */
    public function getLastExecutionDate(CronInterface $cronJob): ?\DateTime
    {
        $lastExecution = $this->getLastExecution($cronJob);
        if (! $lastExecution) {
            return null;
        }

        return $lastExecution->getStartedAt();
    }

    /**
     * Return date of next execution or null if there is no previous run (run needed).
     */
    public function getNextExecutionDate(CronInterface $cronJob): ?\DateTime
    {
        $lastExecutionDate = $this->getLastExecutionDate($cronJob);
        if (! $lastExecutionDate) {
            return null;
        }

        return (new CronExpression($cronJob->getExpression()))
            ->getNextRunDate($this->getLastExecutionDate($cronJob));
    }

    public function isRunNeeded(CronInterface $cronJob): bool
    {
        // Debug log
        $this->logger->debug(sprintf('Checking if execution of %s is needed', $cronJob::class));

        // Check if cron is disabled.
        if (! $cronJob->isActive()) {
            $this->logger->debug(sprintf('%s do not need to run. It\'s disabled.', $cronJob::class));

            return false;
        }

        // Check for pending
        $pendingExcecution = $this->getPendingExecution($cronJob);
        if ($pendingExcecution) {
            $this->logger->debug(sprintf('%s has pending exection. Scheduling it now.', $cronJob::class));
            $this->cleanupPending($cronJob);

            return true;
        }

        // Get next execution date
        $nextExecutionDate = $this->getNextExecutionDate($cronJob);
        if (!$nextExecutionDate) {
            $this->logger->debug(sprintf('%s has no previous run. Scheduling it now.', $cronJob::class));

            return true;
        }

        // Check if parallel execution allowed
        if ($cronJob->isParallelAllowed()) {
            $this->logger->debug(sprintf('%s needs to run, Parallel execution is allowed.', $cronJob::class));

            return true;
        }

        // Check if previous execution still running
        $lastExecution = $this->getLastExecution($cronJob);
        if ($lastExecution && $lastExecution->getState() === Execution::STATE_RUNNING) {
            $this->logger->debug(sprintf('%s has a still running previous execution. Skipping it until previous execution finished.', $cronJob::class));

            return false;
        }
        $this->logger->debug(sprintf('%s needs to run, Parallel execution is not allowed', $cronJob::class));

        return true;
    }

    public function getLastExecution(CronInterface $cronJob): ?Execution
    {
        return $this->repository->findLastExecution($cronJob);
    }

    public function getPendingExecution(CronInterface $cronJob)
    {
        return $this->repository->findPendingExcecution($cronJob);
    }

    protected function schedule(CronInterface $cronJob): void
    {
        $this->logger->info(sprintf('Scheduling execution of %s', $cronJob::class));
        $process = new Process([$this->projectDir . '/bin/console', 'whatwedo:cron:execute', str_replace('\\', '\\\\', $cronJob::class),  '-e', $this->environment]);
        $process->run();
        $this->logger->debug(sprintf('Helper process running with PID %d', $process->getPid()));
    }

    protected function cleanupPending(CronInterface $cronJob): void
    {
        $this->repository->deletePendingJob($cronJob);
        $this->em->flush();
    }

    protected function cleanupStale(): void
    {
        $executions = $this->repository->findByState(Execution::STATE_RUNNING);
        foreach ($executions as $execution) {
            $this->logger->debug(sprintf('Checking execution state with id %d. (%s)', $execution->getPid(), $execution->getJob()));
            if (! posix_kill($execution->getPid(), 0)) {
                $this->logger->warning(sprintf('Marking execution with id %d as stale. (%s)', $execution->getPid(), $execution->getJob()));
                $execution
                    ->setState(Execution::STATE_STALE)
                    ->setPid(null);
                $this->em->flush($execution);
            }
        }
    }
}

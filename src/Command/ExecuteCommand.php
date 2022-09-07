<?php
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

namespace whatwedo\CronBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Attribute\AsCommand;
use whatwedo\CronBundle\CronJob\CronInterface;
use whatwedo\CronBundle\CronJob\CronJobInterface;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Event\CronErrorEvent;
use whatwedo\CronBundle\Event\CronFinishEvent;
use whatwedo\CronBundle\Event\CronStartEvent;
use whatwedo\CronBundle\Exception\MaxRuntimeReachedException;
use whatwedo\CronBundle\Manager\CronJobManager;

#[AsCommand(name: 'whatwedo:cron:execute', description: 'Execute cron job')]
class ExecuteCommand extends Command
{
    public function __construct(
        protected CronJobManager $cronJobManager,
        protected EntityManagerInterface $entityManager,
        protected EventDispatcherInterface $eventDispatcher,
        protected string $projectDir,
        protected string $environment)
    {
        parent::__construct();
    }

    public function checkMaxRuntime(Execution $execution, CronInterface $cronJob, Process $process): void
    {
        if (!$cronJob->getMaxRuntime()) {
            return;
        }
        $now = new DateTime();
        $diff = $now->getTimestamp() - $execution->getStartedAt()->getTimestamp();
        if ($diff > $cronJob->getMaxRuntime()) {
            $execution
                ->setState(Execution::STATE_TERMINATED)
                ->setPid(null)
                ->setStdout($process->getOutput())
                ->setStderr($process->getErrorOutput());
            $this->entityManager->flush($execution);
            throw new MaxRuntimeReachedException($execution);
        }
    }

    protected function configure(): void
    {
        $this
            ->addArgument('cron_job', InputArgument::REQUIRED, 'Class of cron job to execute');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get job definition
        $cronJob = $this->cronJobManager->getCronJob($input->getArgument('cron_job'));

        // Build command to execute
        $command = array_merge(['bin/console', $this->getCronCommand($cronJob), '--env='.$this->environment], $this->getCronArguments($cronJob));

        // Create execution
        $execution = new Execution();
        $execution->setJob($cronJob::class)
            ->setCommand($command);
        $this->entityManager->persist($execution);
        $this->entityManager->flush($execution);

        // Run command
        $process = new Process($command, $this->projectDir);
        $process->start();
        $execution->setPid($process->getPid());
        $this->entityManager->flush($execution);
        $this->eventDispatcher->dispatch(new CronStartEvent($cronJob), CronStartEvent::NAME);

        // Update command output every 5 seconds
        while ($process->isRunning()) {
            $this->checkMaxRuntime($execution, $cronJob, $process);
            $output->writeln(sprintf('Process %s is running...', $process->getCommandLine()));
            sleep(5);
            $execution->setStdout($process->getOutput())
                ->setStderr($process->getErrorOutput());
            $this->entityManager->flush($execution);
        }

        if (!$process->isSuccessful()) {
            $this->eventDispatcher->dispatch(new CronErrorEvent($cronJob, $process->getErrorOutput()), CronErrorEvent::NAME);
        }

        // Finish execution
        $output->writeln('Execution finished with exit code '.$process->getExitCode());
        $execution
            ->setState(Execution::STATE_FINISHED)
            ->setFinishedAt(new DateTime())
            ->setPid(null)
            ->setStdout($process->getOutput())
            ->setStderr($process->getErrorOutput())
            ->setExitCode($process->getExitCode());
        $this->entityManager->flush($execution);
        $this->eventDispatcher->dispatch(new CronFinishEvent($cronJob), CronFinishEvent::NAME);
        return Command::SUCCESS;
    }

    protected function getCronCommand(CronInterface $cron): string
    {
        if ($cron instanceof CronJobInterface) {
            return $cron->getCommand();
        }

        if ($cron instanceof Command) {
            return $cron->getDefaultName();
        }

        return '';
    }

    /**
     * @return string[]
     */
    protected function getCronArguments(CronInterface $cron): array
    {
        if ($cron instanceof CronJobInterface) {
            return $cron->getArguments();
        }

        return [];
    }
}

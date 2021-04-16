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

use whatwedo\CronBundle\CronJob\CronJobInterface;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Exception\MaxRuntimeReachedException;
use whatwedo\CronBundle\Manager\CronJobManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Class ExecuteCommand
 *
 * @package whatwedo\CronBundle\Command
 */
class ExecuteCommand extends Command
{
    /**
     * @var CronJobManager
     */
    protected $cronJobManager;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var string
     */
    protected $projectDir;

    /**
     * @var string
     */
    protected $environment;

    /**
     * ExecuteCommand constructor.
     *
     * @param CronJobManager $cronJobManager
     * @param EntityManagerInterface $em
     * @param string $projectDir
     * @param string $environment
     */
    public function __construct(CronJobManager $cronJobManager, EntityManagerInterface $em, string $projectDir, string $environment)
    {
        parent::__construct();
        $this->em = $em;
        $this->projectDir = $projectDir;
        $this->cronJobManager = $cronJobManager;
        $this->environment = $environment;
    }

    /**
     * @param Execution $execution
     * @param CronJobInterface $cronJob
     * @param Process $process
     */
    public function checkMaxRuntime(Execution $execution, CronJobInterface $cronJob, Process $process): void
    {
        if (!$cronJob->getMaxRuntime()) {
            return;
        }
        $now = new \DateTime();
        $diff = $now->getTimestamp() - $execution->getStartedAt()->getTimestamp();
        if ($diff > $cronJob->getMaxRuntime()) {
            $execution
                ->setState(Execution::STATE_TERMINATED)
                ->setPid(null)
                ->setStdout($process->getOutput())
                ->setStderr($process->getErrorOutput());
            $this->em->flush($execution);
            throw new MaxRuntimeReachedException($execution);
        }
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('whatwedo:cron:execute')
            ->setDescription('Execute cron job (internal use only)')
            ->addArgument('cron_job', InputArgument::REQUIRED, 'Class of cron job to execute')
            ->setHidden(true);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get job definition
        $cronJob = $this->cronJobManager->getCronJob($input->getArgument('cron_job'));

        // Build command to execute
        $arguments = $this->prepareArguments($cronJob);
        $command = array_merge(['bin/console', $cronJob->getCommand(), '--env='.$this->environment], $arguments);

        // Create execution
        $execution = new Execution();
        $execution->setClass(get_class($cronJob))
            ->setCommand($command);
        $this->em->persist($execution);
        $this->em->flush($execution);

        // Run command
        $process = new Process($command, $this->projectDir);
        $process->start();
        $execution->setPid($process->getPid());
        $this->em->flush($execution);

        // Update command output every 5 seconds
        while ($process->isRunning()) {
            $this->checkMaxRuntime($execution, $cronJob, $process);
            $output->writeln(sprintf('Process %s is running...', $process->getCommandLine()));
            sleep(5);
            $execution->setStdout($process->getOutput())
                ->setStderr($process->getErrorOutput());
            $this->em->flush($execution);
        }

        // Finish execution
        $output->writeln('Execution finished with exit code '.$process->getExitCode());
        $execution
            ->setState(Execution::STATE_FINISHED)
            ->setFinishedAt(new \DateTime())
            ->setPid(null)
            ->setStdout($process->getOutput())
            ->setStderr($process->getErrorOutput())
            ->setExitCode($process->getExitCode());
        $this->em->flush($execution);
    }

    /**
     * @param string[] $arguments
     */
    protected function prepareArguments(CronJobInterface $cronJob) {
        $arguments = $cronJob->getArguments();
        $lastExecution = $this->getLastExecution($cronJob);
        if ($lastExecution && $arguments && in_array('--last-run', $arguments)) {
            // add last-run timestamp
            $index = array_search('--last-run', $arguments);
            array_splice($arguments, $index + 1, 0, $lastExecution->getStartedAt()->getTimestamp());
        } else if (!$lastExecution && $arguments && in_array('--last-run', $arguments)) {
            // remove --last-run argument if timestamp not available
            $index = array_search('--last-run', $arguments);
            unset($arguments[$index]);
        }
        return $arguments;
    }

    /**
     * @param CronJobInterface $cronJob
     *
     * @return Execution|null
     */
    public function getLastExecution(CronJobInterface $cronJob): ?Execution
    {
        return $this->em->getRepository(Execution::class)->findLastExecution($cronJob);
    }
}

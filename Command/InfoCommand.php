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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CronBundle\CronJob\CronJobInterface;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Manager\CronJobManager;
use whatwedo\CronBundle\Manager\ExecutionManager;

/**
 * Class InfoCommand
 */
class InfoCommand extends Command
{
    protected static $defaultName = 'whatwedo:cron:info';
    /**
     * @var CronJobManager
     */
    protected $cronJobManager;

    /**
     * @var ExecutionManager
     */
    protected $executionManager;

    /**
     * InfoCommand constructor.
     */
    public function __construct(CronJobManager $cronJobManager, ExecutionManager $executionManager)
    {
        parent::__construct();
        $this->cronJobManager = $cronJobManager;
        $this->executionManager = $executionManager;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('whatwedo:cron:info')
            ->setDescription('Print information about the given cron job')
            ->addArgument('cron_job', InputArgument::REQUIRED, 'Class of cron job to execute');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get job definition
        $cronJob = $this->cronJobManager->getCronJob($input->getArgument('cron_job'));

        // Build table
        $table = new Table($output);
        $table->setHeaders(['Name', 'Value']);
        $table->addRows([
            ['Cron job', get_class($cronJob)],
            ['Description', $cronJob->getDescription()],
            ['Expression', $cronJob->getExpression()],
            ['Command', $cronJob->getCommand()],
            ['Arguments', implode(' ', $cronJob->getArguments())],
            ['Last execution', $this->getLastExecutionDateString($cronJob)],
            ['Next execution', $this->getNextExecutionDateString($cronJob)],
            ['Max runtime', $cronJob->getMaxRuntime().' seconds'],
            ['Lock status', $this->getLockStatus($cronJob)],
        ]);

        // Render table
        $table->render();
        return 0;
    }

    /**
     * @param CronJobInterface $cronJob
     */
    protected function getLastExecutionDateString(CronJobInterface $cronJob): ?string
    {
        $lastExecutionDate = $this->executionManager->getLastExecutionDate($cronJob);
        if (!$lastExecutionDate) {
            return null;
        }
        return $this->getFormattedDate($lastExecutionDate);
    }

    /**
     * @param CronJobInterface $cronJob
     */
    protected function getNextExecutionDateString(CronJobInterface $cronJob): ?string
    {
        if (false === $cronJob->isActive()) {
            return 'Disabled';
        }
        $nextExecutionDate = $this->executionManager->getNextExecutionDate($cronJob);
        if (!$nextExecutionDate) {
            return null;
        }
        $now = new DateTime();
        if ($nextExecutionDate < $now) {
            return 'Now';
        }
        return $this->getFormattedDate($nextExecutionDate);
    }

    /**
     * @param \DateTime $date
     */
    protected function getFormattedDate(DateTime $date): ?string
    {
        if (!$date) {
            return null;
        }
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @param CronJobInterface $cronJob
     */
    protected function getLockStatus(CronJobInterface $cronJob): string
    {
        if ($cronJob->isParallelAllowed()) {
            return 'Parallel execution allowed';
        }

        $lastExecution = $this->executionManager->getLastExecution($cronJob);
        if ($lastExecution && $lastExecution->getState() == Execution::STATE_RUNNING) {
            return 'Locked';
        }
        return 'Not locked';
    }
}

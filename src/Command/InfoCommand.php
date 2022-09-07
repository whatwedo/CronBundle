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
use Symfony\Component\Console\Attribute\AsCommand;
use whatwedo\CronBundle\CronJob\CronInterface;
use whatwedo\CronBundle\CronJob\CronJobInterface;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Manager\CronJobManager;
use whatwedo\CronBundle\Manager\ExecutionManager;

#[AsCommand(name: 'whatwedo:cron:info', description: 'Print information about the given cron job')]
class InfoCommand extends Command
{
    protected CronJobManager $cronJobManager;

    protected ExecutionManager $executionManager;

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
        $this
            ->addArgument('cron_job', InputArgument::REQUIRED, 'Class of cron job to execute');
    }

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
            ['Command', $this->getCommand($cronJob)],
            ['Arguments', $this->getArgumentString($cronJob)],
            ['Last execution', $this->getLastExecutionDateString($cronJob)],
            ['Next execution', $this->getNextExecutionDateString($cronJob)],
            ['Max runtime', $cronJob->getMaxRuntime().' seconds'],
            ['Lock status', $this->getLockStatus($cronJob)],
        ]);

        // Render table
        $table->render();
        return Command::SUCCESS;
    }

    protected function getCommand(CronInterface $cronJob): string
    {
        if ($cronJob instanceof CronJobInterface) {
            return $cronJob->getCommand();
        }

        if ($cronJob instanceof Command) {
            return $cronJob->getDefaultName();
        }

        return '';
    }

    protected function getArgumentString(CronInterface $cronJob): string
    {
        if ($cronJob instanceof CronJobInterface) {
            return implode(' ', $cronJob->getArguments());
        }

        return '';
    }

    protected function getLastExecutionDateString(CronInterface $cronJob): ?string
    {
        $lastExecutionDate = $this->executionManager->getLastExecutionDate($cronJob);
        if (!$lastExecutionDate) {
            return null;
        }
        return $this->getFormattedDate($lastExecutionDate);
    }

    protected function getNextExecutionDateString(CronInterface $cronJob): ?string
    {
        if (!$cronJob->isActive()) {
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

    protected function getFormattedDate(DateTime $date): ?string
    {
        if (!$date) {
            return null;
        }
        return $date->format('Y-m-d H:i:s');
    }

    protected function getLockStatus(CronInterface $cronJob): string
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

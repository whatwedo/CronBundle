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

use Cron\CronExpression;
use whatwedo\CronBundle\Manager\CronJobManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 *
 * @package whatwedo\CronBundle\Command
 */
class ListCommand extends Command
{
    /**
     * @var CronJobManager
     */
    protected $cronJobManager;

    /**
     * ListCommand constructor.
     *
     * @param CronJobManager $cronJobManager
     */
    public function __construct(CronJobManager $cronJobManager)
    {
        parent::__construct();
        $this->cronJobManager = $cronJobManager;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('whatwedo:cron:list')
            ->setDescription('List all cron jobs');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(['Cron job', 'Description','Next Run']);
        foreach ($this->cronJobManager->getCronJobs() as $cronJob) {

            $nextRunDate = 'invalid cron expression';
            if (CronExpression::isValidExpression($cronJob->getExpression())) {
                $cronExpression = CronExpression::factory($cronJob->getExpression());
                $nextRunDate = $cronExpression->getNextRunDate()->format('Y-m-d H:i:s');
            }

            $table->addRow([get_class($cronJob), $cronJob->getDescription(), $nextRunDate]);
        }
        $table->render();

        return Command::SUCCESS;
    }
}

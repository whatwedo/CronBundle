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

use whatwedo\CronBundle\Manager\ExecutionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class SchedulerCommand
 *
 * @package whatwedo\CronBundle\Command
 */
class SchedulerCommand extends Command
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ExecutionManager
     */
    protected $executionManager;

    /**
     * SchedulerCommand constructor.
     *
     * @param LoggerInterface $logger
     * @param ExecutionManager $executionManager
     */
    public function __construct(LoggerInterface $logger, ExecutionManager $executionManager)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->executionManager = $executionManager;
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();
        $this->setName('whatwedo:cron:scheduler')
            ->setDescription('Run scheduler process');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Set the max amount of memory to prevent memory leak (initial memory usage * 2)
        $maxMemory = memory_get_usage() * 2;
        $this->logger->debug(sprintf('Setting max amount of scheduler memory to %d', $maxMemory));
        ini_set('memory_limit', $maxMemory);

        // Check cron jobs every 15s
        try {
            while (true) {
                $this->executionManager->check();
                sleep(15);
            }
        } catch (\Exception $ex) {
            $this->logger->warning($ex->getMessage());
        }
    }
}

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

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Repository\ExecutionRepository;

/**
 * Class SchedulerCommand
 */
class CleanupCommand extends Command
{
    protected static $defaultName = 'whatwedo:cron:cleanup';
    /**
     * @var ExecutionRepository
     */
    protected $executionRepository;

    /**
     * CleanupCommand constructor.
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->executionRepository = $entityManager->getRepository(Execution::class);
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('whatwedo:cron:cleanup')
            ->setDescription('Cleans up jobs which exceed the maximum retention time')
            ->addOption('max-retention', null, InputOption::VALUE_REQUIRED, 'The maximum retention time (will be parsed by DateTime).', '1 month')
            ->addOption('max-retention-successful', null, InputOption::VALUE_REQUIRED, 'The maximum retention time for succeeded jobs (will be parsed by DateTime).', '7 days')
            ->addOption('per-call', null, InputOption::VALUE_REQUIRED, 'The maximum number of jobs to clean-up per call.', 1000)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $perCall = (int) $input->getOption('per-call');

        $deletedSuccessful = $this->executionRepository->deleteSuccessfulJobs(
            new DateTimeImmutable('-' . $input->getOption('max-retention-successful')),
            $perCall
        );

        $output->writeln(sprintf(
            '- deleted <info>%s</info> successful job execution logs',
            $deletedSuccessful
        ));

        $deletedNotSuccessful = $this->executionRepository->deleteNotSuccessfulJobs(
            new DateTimeImmutable('-' . $input->getOption('max-retention')),
            $perCall
        );

        $output->writeln(sprintf(
            '- deleted <info>%s</info> not successful job execution logs',
            $deletedNotSuccessful
        ));
        return 0;
    }
}

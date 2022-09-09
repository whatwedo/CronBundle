<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NotCronJobCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:not_cron');
        $this->setDescription('Runs successful');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}

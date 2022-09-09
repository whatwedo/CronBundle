<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use whatwedo\CronBundle\CronJob\CronJobInterface;

class SuccessfulCommand extends Command implements CronJobInterface
{
    public function getCommand(): string
    {
        return $this->getName();
    }

    public function getExpression(): string
    {
        return '* * * * *';
    }

    public function getArguments(): array
    {
        return [];
    }

    public function getMaxRuntime(): ?int
    {
        return null;
    }

    public function isParallelAllowed(): bool
    {
        return false;
    }

    public function isActive(): bool
    {
        return true;
    }

    protected function configure(): void
    {
        $this->setName('app:successful');
        $this->setDescription('Runs successful');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}

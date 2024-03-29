<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\App\CronJob;

use whatwedo\CronBundle\CronJob\AbstractCronJob;

class DemoCron extends AbstractCronJob
{
    public function getDescription(): ?string
    {
        return 'Is only a test Cron Job';
    }

    public function getArguments(): array
    {
        return parent::getArguments();
    }

    public function getCommand(): string
    {
        return 'list';
    }

    public function getExpression(): string
    {
        return '*/5 * * * * ';
    }
}

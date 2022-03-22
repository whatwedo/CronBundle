<?php

namespace whatwedo\CronBundle\Tests\App\CronJob;

use whatwedo\CronBundle\CronJob\AbstractCronJob;

class DemoCron extends AbstractCronJob
{
    public function getCommand(): string
    {
        return 'app:my-command';
    }

    public function getExpression(): string
    {
        return '*/1 * * * * ';
    }
}
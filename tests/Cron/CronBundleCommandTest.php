<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Cron;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;

class CronBundleCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    public function testListCommand()
    {
        $this->executeConsoleCommand('whatwedo:cron:list')
            ->assertSuccessful() // command exit code is 0
            ->assertOutputContains('Is only a test Cron Job')
        ;
    }

    public function testInfoCommand()
    {
        $this->executeConsoleCommand("whatwedo:cron:info whatwedo\\\CronBundle\\\Tests\\\App\\\CronJob\\\DemoCron")
            ->assertSuccessful() // command exit code is 0
        ;
    }

    public function testExecuteCommand()
    {
        $this->executeConsoleCommand("whatwedo:cron:execute whatwedo\\\CronBundle\\\Tests\\\App\\\CronJob\\\DemoCron")
            ->assertSuccessful() // command exit code is 0
        ;
    }

    public function testCheckCommand()
    {
        $this->executeConsoleCommand('whatwedo:cron:check')
            ->assertSuccessful() // command exit code is 0
        ;
    }

    public function testCleanupCommand()
    {
        $this->executeConsoleCommand('whatwedo:cron:cleanup')
            ->assertSuccessful() // command exit code is 0
        ;
    }

    public function testSchedulerCommand()
    {
        $this->executeConsoleCommand('whatwedo:cron:scheduler --max-runtime 60')
            ->assertSuccessful() // command exit code is 0
        ;
    }
}

<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\CronBundle\Manager\ExecutionManager;

class ExecutionManagerTest extends KernelTestCase
{
    public function testCheck(): void
    {
        /** @var ExecutionManager $executionManager */
        $executionManager = self::getContainer()->get(ExecutionManager::class);

        $executionManager->check();
        $this->assertTrue(true);
        $this->markTestSkipped('todo');
    }
}

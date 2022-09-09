<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Functional\Manager;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\CronBundle\Exception\CronJobNotFoundException;
use whatwedo\CronBundle\Manager\CronJobManager;
use whatwedo\CronBundle\Tests\App\Command\FailingCommand;
use whatwedo\CronBundle\Tests\App\Command\ListCommand;
use whatwedo\CronBundle\Tests\App\Command\NotCronJobCommand;
use whatwedo\CronBundle\Tests\App\Command\SuccessfulCommand;
use whatwedo\CronBundle\Tests\App\CronJob\ListCron;

class CronJobManagerTest extends KernelTestCase
{
    public function testGetJob(): void
    {
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->assertInstanceOf(ListCron::class, $cronJobManager->getCronJob(ListCron::class));
        $this->assertInstanceOf(SuccessfulCommand::class, $cronJobManager->getCronJob(SuccessfulCommand::class));
        $this->assertInstanceOf(FailingCommand::class, $cronJobManager->getCronJob(FailingCommand::class));
    }

    public function testCommandByCronJob(): void
    {
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->assertInstanceOf(ListCommand::class, $cronJobManager->getCommandByCronJob(new ListCron($cronJobManager)));
    }

    public function testGetUnknowJob(): void
    {
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->expectException(CronJobNotFoundException::class);
        $cronJobManager->getCronJob(NotCronJobCommand::class);
    }
}

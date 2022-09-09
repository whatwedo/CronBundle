<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Manager\CronJobManager;
use whatwedo\CronBundle\Repository\ExecutionRepository;
use whatwedo\CronBundle\Tests\App\CronJob\ListCron;
use whatwedo\CronBundle\Tests\App\Factory\ExecutionFactory;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    public function testDeleteSuccessful(): void
    {
        $this->createExecutions();
        /** @var ExecutionRepository $executionRepository */
        $executionRepository = self::getContainer()->get(ExecutionRepository::class);
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->assertCount(60, $executionRepository->findAll());

        $this->assertSame(20, $executionRepository->deleteExecutions(new ListCron($cronJobManager), 'successful'));
    }

    public function testDeletePending(): void
    {
        $this->createExecutions();
        /** @var ExecutionRepository $executionRepository */
        $executionRepository = self::getContainer()->get(ExecutionRepository::class);
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->assertCount(60, $executionRepository->findAll());

        $this->assertSame(10, $executionRepository->deleteExecutions(new ListCron($cronJobManager), 'pending'));
    }

    public function testDeleteError(): void
    {
        $this->createExecutions();
        /** @var ExecutionRepository $executionRepository */
        $executionRepository = self::getContainer()->get(ExecutionRepository::class);
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->assertCount(60, $executionRepository->findAll());

        $this->assertSame(10, $executionRepository->deleteExecutions(new ListCron($cronJobManager), 'failed'));
    }

    public function testDeleteNotSuccesful(): void
    {
        $this->createExecutions();
        /** @var ExecutionRepository $executionRepository */
        $executionRepository = self::getContainer()->get(ExecutionRepository::class);
        /** @var CronJobManager $cronJobManager */
        $cronJobManager = self::getContainer()->get(CronJobManager::class);

        $this->assertCount(60, $executionRepository->findAll());

        $this->assertSame(10, $executionRepository->deleteNotSuccessfulJobs(new \DateTime('now')));
    }

    protected function createExecutions(): void
    {
        ExecutionFactory::createMany(10, [
            'job' => ListCron::class,
            'state' => Execution::STATE_FINISHED,
        ]);
        ExecutionFactory::createMany(10, [
            'job' => ListCron::class,
            'state' => Execution::STATE_ERROR,
        ]);
        ExecutionFactory::createMany(10, [
            'job' => ListCron::class,
            'state' => Execution::STATE_PENDING,
        ]);
        ExecutionFactory::createMany(10, [
            'job' => ListCron::class,
            'state' => Execution::STATE_STALE,
        ]);
        ExecutionFactory::createMany(10, [
            'job' => ListCron::class,
            'state' => Execution::STATE_TERMINATED,
        ]);
        ExecutionFactory::createMany(10, [
            'job' => ListCron::class,
            'state' => Execution::STATE_RUNNING,
        ]);
    }
}

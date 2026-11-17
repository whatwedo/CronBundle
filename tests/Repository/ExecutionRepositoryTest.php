<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Manager\CronJobManager;
use whatwedo\CronBundle\Repository\ExecutionRepository;
use whatwedo\CronBundle\Tests\App\CronJob\DemoCron;
use Zenstruck\Console\Test\InteractsWithConsole;

class ExecutionRepositoryTest extends KernelTestCase
{
    use InteractsWithConsole;

    private EntityManagerInterface $entityManager;

    private ExecutionRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::service(EntityManagerInterface::class);
        $this->repository = self::service(ExecutionRepository::class);

        $this->entityManager->createQuery(sprintf('DELETE FROM %s e', Execution::class))->execute();
    }

    public function testExecutionRoundTripKeepsDatesAndLoadsCronJob(): void
    {
        $execution = $this->createExecution(Execution::STATE_FINISHED, new \DateTime('2026-01-02 03:04:05'));
        $execution->setFinishedAt(new \DateTime('2026-01-02 03:05:06'));
        $this->entityManager->flush();
        $id = $execution->getId();
        $this->entityManager->clear();

        $reloaded = $this->repository->find($id);

        self::assertInstanceOf(Execution::class, $reloaded);
        self::assertInstanceOf(\DateTime::class, $reloaded->getStartedAt());
        self::assertSame('2026-01-02 03:04:05', $reloaded->getStartedAt()->format('Y-m-d H:i:s'));
        self::assertSame('2026-01-02 03:05:06', $reloaded->getFinishedAt()?->format('Y-m-d H:i:s'));
        // set by the postLoad listener
        self::assertInstanceOf(DemoCron::class, $reloaded->getCronJob());
    }

    public function testFindNewestIdsByJobAndStateReturnsNewestFirst(): void
    {
        $oldest = $this->createExecution(Execution::STATE_FINISHED, new \DateTime('-3 hours'));
        $middle = $this->createExecution(Execution::STATE_FINISHED, new \DateTime('-2 hours'));
        $newest = $this->createExecution(Execution::STATE_FINISHED, new \DateTime('-1 hour'));
        $this->createExecution(Execution::STATE_ERROR, new \DateTime());
        $this->entityManager->flush();

        $cronJob = self::service(CronJobManager::class)->getCronJob(DemoCron::class);
        $ids = $this->repository->findNewestIdsByJobAndState($cronJob, Execution::STATE_FINISHED, 2);

        self::assertSame([$newest->getId(), $middle->getId()], $ids);
        self::assertNotContains($oldest->getId(), $ids);
    }

    public function testCleanupCommandRemovesOnlyExpiredExecutions(): void
    {
        $expired = $this->createExecution(Execution::STATE_FINISHED, new \DateTime('-10 days'));
        $fresh = $this->createExecution(Execution::STATE_FINISHED, new \DateTime('-1 day'));
        $expiredStale = $this->createExecution(Execution::STATE_STALE, new \DateTime('-2 months'));
        $freshStale = $this->createExecution(Execution::STATE_STALE, new \DateTime('-1 week'));
        $this->entityManager->flush();
        $expiredIds = [$expired->getId(), $expiredStale->getId()];
        $freshIds = [$fresh->getId(), $freshStale->getId()];
        $this->entityManager->clear();

        $this->executeConsoleCommand('whatwedo:cron:cleanup')
            ->assertSuccessful()
            ->assertOutputContains('deleted 1 successful job execution logs')
            ->assertOutputContains('deleted 1 stale job execution logs')
        ;

        foreach ($expiredIds as $id) {
            self::assertNull($this->repository->find($id));
        }
        foreach ($freshIds as $id) {
            self::assertNotNull($this->repository->find($id));
        }
    }

    private function createExecution(string $state, \DateTime $startedAt): Execution
    {
        $execution = (new Execution())
            ->setJob(DemoCron::class)
            ->setState($state)
            ->setExitCode($state === Execution::STATE_FINISHED ? 0 : 1)
            ->setStartedAt($startedAt);
        $this->entityManager->persist($execution);

        return $execution;
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private static function service(string $class): object
    {
        $service = self::getContainer()->get($class);
        if (! $service instanceof $class) {
            throw new \LogicException(sprintf('Service %s not found.', $class));
        }

        return $service;
    }
}

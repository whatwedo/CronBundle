<?php

declare(strict_types=1);

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

namespace whatwedo\CronBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use whatwedo\CronBundle\CronJob\CronInterface;
use whatwedo\CronBundle\Entity\Execution;

/**
 * @extends ServiceEntityRepository<Execution>
 */
class ExecutionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Execution::class);
    }

    /**
     * @return Execution[]
     */
    public function findByState(string $state): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.state = :state')
            ->setParameter('state', $state)
            ->getQuery()
            ->disableResultCache()
            ->getResult();
    }

    /**
     * @return Execution[]
     */
    public function findByJob(CronInterface $cronJob): array
    {
        // findBy() keeps accepting string sort directions on every ORM version,
        // whereas QueryBuilder::orderBy() wants \SortDirection since ORM 3.6.
        return $this->findBy([
            'job' => $cronJob::class,
        ], [
            'startedAt' => 'DESC',
        ]);
    }

    public function findLastExecution(CronInterface $cronJob): ?Execution
    {
        return $this->findOneBy([
            'job' => $cronJob::class,
        ], [
            'startedAt' => 'DESC',
        ]);
    }

    /**
     * Returns the ids of the newest executions of a job in the given state.
     *
     * @return int[]
     */
    public function findNewestIdsByJobAndState(CronInterface $cronJob, string $state, int $limit): array
    {
        // Select only the ids, the executions' output columns can be large.
        $rows = $this->getEntityManager()
            ->createQuery(sprintf(
                'SELECT e.id FROM %s e WHERE e.job = :job AND e.state = :state ORDER BY e.startedAt DESC',
                Execution::class,
            ))
            ->setParameter('job', $cronJob::class)
            ->setParameter('state', $state)
            ->setMaxResults($limit)
            ->getScalarResult();

        return array_map(static fn(array $row): int => (int) $row['id'], $rows);
    }

    /**
     * @return Execution[]
     */
    public function findNonPendingExcecution(CronInterface $cronJob): array
    {
        return $this->getEntityManager()
            ->createQuery(sprintf(
                'SELECT e FROM %s e WHERE e.job = :job AND e.state != :statePending ORDER BY e.startedAt DESC',
                Execution::class,
            ))
            ->setParameter('job', $cronJob::class)
            ->setParameter('statePending', Execution::STATE_PENDING)
            ->getResult();
    }

    /**
     * @return Execution[]
     */
    public function findPendingExecution(CronInterface $cronJob): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.job = :job')
            ->andWhere('e.state = :statePending')
            ->setParameter('job', $cronJob::class)
            ->setParameter('statePending', Execution::STATE_PENDING)
            ->getQuery()
            ->disableResultCache()
            ->getResult();
    }

    public function deletePendingJob(CronInterface $cronJob): int
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.job = :job')
            ->andWhere('e.state = :statePending')
            ->setParameter('job', $cronJob::class)
            ->setParameter('statePending', Execution::STATE_PENDING)
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteSuccessfulJobs(\DateTimeInterface $retention): int
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.startedAt < :retention')
            ->andWhere('e.state = :stateSuccessful')
            ->andWhere('e.exitCode = 0')
            ->setParameter('retention', $retention)
            ->setParameter('stateSuccessful', Execution::STATE_FINISHED)
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteNotSuccessfulJobs(\DateTimeInterface $retention): int
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.startedAt < :retention')
            ->andWhere('e.state IN (:stateSuccessful)')
            ->andWhere('e.exitCode != 0')
            ->setParameter('retention', $retention)
            ->setParameter('stateSuccessful', [
                Execution::STATE_FINISHED,
                Execution::STATE_TERMINATED,
                Execution::STATE_TERMINATED,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteStaleJobs(\DateTimeInterface $retention): int
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.startedAt < :retention')
            ->andWhere('e.state = :stateStale')
            ->setParameter('retention', $retention)
            ->setParameter('stateStale', Execution::STATE_STALE)
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteExecutions(CronInterface $cronJob, string $state): int
    {
        $states = match ($state) {
            'successful' => [
                Execution::STATE_FINISHED,
                Execution::STATE_TERMINATED,
            ],
            'failed', => [
                Execution::STATE_ERROR,
            ],
            'pending' => [
                Execution::STATE_PENDING,
            ],
            default => [],
        };

        $queryBuilder = $this->createQueryBuilder('e')
            ->delete()
            ->where('e.job = :job')
            ->andWhere('e.state IN (:states)');

        return $queryBuilder
            ->setParameter('job', $cronJob::class)
            ->setParameter('states', $states)
            ->getQuery()
            ->execute()
        ;
    }
}

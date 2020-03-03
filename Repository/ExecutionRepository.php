<?php
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

use DateTimeInterface;
use whatwedo\CronBundle\CronJob\CronJobInterface;
use whatwedo\CronBundle\Entity\Execution;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;

/**
 * Class ExecutionRepository
 */
class ExecutionRepository extends EntityRepository
{
    /**
     * @return Collection|Execution[]
     */
    public function findByState(string $state)
    {
        return $this->createQueryBuilder('e')
            ->where('e.state = :state')
            ->setParameter('state', $state)
            ->getQuery()
            ->getResult();
    }

    public function findLastExecution(CronJobInterface $cronJob): ?Execution
    {
        return $this->createQueryBuilder('e')
            ->where('e.job = :job')
            ->orderBy('e.startedAt', 'DESC')
            ->setMaxResults(1)
            ->setParameter('job', get_class($cronJob))
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function deleteSuccessfulJobs(DateTimeInterface $retention, $limit = null)
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.startedAt < :retention')
            ->andWhere('e.state = :stateSuccessful')
            ->andWhere('e.exitCode = 0')
            ->setParameters([
                'retention' => $retention,
                'stateSuccessful' => Execution::STATE_FINISHED,
            ])
            ->getQuery()
            ->execute()
        ;
    }

    public function deleteNotSuccessfulJobs(DateTimeInterface $retention, $limit = null)
    {
        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.startedAt < :retention')
            ->andWhere('e.state IN (:stateSuccessful)')
            ->andWhere('e.exitCode != 0')
            ->setParameters([
                'retention' => $retention,
                'stateSuccessful' => [
                    Execution::STATE_FINISHED,
                    Execution::STATE_TERMINATED,
                    Execution::STATE_TERMINATED
                ],
            ])
            ->getQuery()
            ->execute()
        ;
    }
}

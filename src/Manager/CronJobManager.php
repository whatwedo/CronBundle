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

namespace whatwedo\CronBundle\Manager;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpKernel\KernelInterface;
use whatwedo\CronBundle\CronJob\CronInterface;
use whatwedo\CronBundle\CronJob\CronJobInterface;
use whatwedo\CronBundle\Exception\CronJobNotFoundException;

class CronJobManager
{
    /**
     * @var Application
     */
    protected $consoleApplication;

    /**
     * @var CronInterface[]
     */
    protected $cronJobs = [];

    public function __construct(KernelInterface $kernel)
    {
        $this->consoleApplication = new Application($kernel);
    }

    public function addCronJob(CronInterface $cronJob): self
    {
        $this->cronJobs[$cronJob::class] = $cronJob;

        return $this;
    }

    /**
     * @return CronInterface[]
     */
    public function getCronJobs(): array
    {
        return $this->cronJobs;
    }

    public function getCronJob(string $class): CronInterface
    {
        foreach ($this->cronJobs as $cronJob) {
            if ($cronJob instanceof $class) {
                return $cronJob;
            }
        }
        throw new CronJobNotFoundException($class);
    }

    public function getCommandByCronJob(CronJobInterface $cronJob): Command
    {
        return $this->consoleApplication->find($cronJob->getCommand());
    }
}

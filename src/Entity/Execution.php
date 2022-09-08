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

namespace whatwedo\CronBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use whatwedo\CronBundle\CronJob\CronInterface;

/**
 * Class Execution.
 *
 * @ORM\Table(name="whatwedo_cron_execution")
 * @ORM\Entity(repositoryClass="whatwedo\CronBundle\Repository\ExecutionRepository")
 */
class Execution
{
    public const STATE_PENDING = 'pending';

    public const STATE_RUNNING = 'running';

    public const STATE_FINISHED = 'finished';

    public const STATE_STALE = 'stale';

    public const STATE_TERMINATED = 'terminated';

    public const STATE_ERROR = 'error';

    /**
     * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected ?int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $state = self::STATE_RUNNING;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected string $job;

    /**
     * @ORM\Column(type="json", nullable=false)
     */
    protected ?array $command = [];

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected \DateTime $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected \DateTime $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected ?\DateTime $finishedAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $pid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected ?int $exitCode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $stdout = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected ?string $stderr = null;

    /**
     * @var CronInterface|null
     */
    protected $cronJob;

    public function __construct()
    {
        $this->startedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getJob(): ?string
    {
        return $this->job;
    }

    public function setJob(?string $job): self
    {
        $this->job = $job;

        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getCommand(): ?array
    {
        return $this->command;
    }

    /**
     * @param string[]|null $command
     */
    public function setCommand(?array $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTime $finishedAt): self
    {
        $this->finishedAt = $finishedAt;

        return $this;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(?int $pid): self
    {
        $this->pid = $pid;

        return $this;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    public function setExitCode(?int $exitCode): self
    {
        $this->exitCode = $exitCode;

        return $this;
    }

    public function getStdout(): ?string
    {
        return $this->stdout;
    }

    public function setStdout(?string $stdout): self
    {
        $this->stdout = $stdout;

        return $this;
    }

    public function getStderr(): ?string
    {
        return $this->stderr;
    }

    public function setStderr(?string $stderr): self
    {
        $this->stderr = $stderr;

        return $this;
    }

    public function getCronJob(): ?CronInterface
    {
        return $this->cronJob;
    }

    public function setCronJob(?CronInterface $cronJob): self
    {
        $this->cronJob = $cronJob;

        return $this;
    }

    public function __toString(): string
    {
        return '#' . str_pad($this->getId(), 6, '0', STR_PAD_LEFT);
    }
}

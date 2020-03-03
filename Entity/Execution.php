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

namespace whatwedo\CronBundle\Entity;

use DateTime;
use whatwedo\CronBundle\CronJob\CronJobInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Execution
 *
 * @ORM\Table(name="whatwedo_cron_execution")
 * @ORM\Entity(repositoryClass="whatwedo\CronBundle\Repository\ExecutionRepository")
 */
class Execution
{
    const STATE_RUNNING = 'running';

    const STATE_FINISHED = 'finished';

    const STATE_STALE = 'stale';

    const STATE_TERMINATED = 'terminated';

    /**
     * @var int|null
     *
     * @ORM\Column(type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $state = self::STATE_RUNNING;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $job;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="json", nullable=false)
     */
    protected $command = [];

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $startedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $updatedAt;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $finishedAt;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $pid;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $exitCode;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    protected $stdout;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string|null
     */
    protected $stderr;

    /**
     * @var CronJobInterface|null
     */
    protected $cronJob;

    /**
     * Execution constructor.
     */
    public function __construct()
    {
        $this->startedAt = new DateTime();
        $this->updatedAt = new DateTime();
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

    public function getStartedAt(): ?DateTime
    {
        return $this->startedAt;
    }

    public function setStartedAt(?DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getFinishedAt(): ?DateTime
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?DateTime $finishedAt): self
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

    public function getCronJob(): ?CronJobInterface
    {
        return $this->cronJob;
    }

    public function setCronJob(?CronJobInterface $cronJob): self
    {
        $this->cronJob = $cronJob;
        return $this;
    }

    public function __toString(): string
    {
        return '#'.str_pad($this->getId(), 6, '0', STR_PAD_LEFT);
    }
}

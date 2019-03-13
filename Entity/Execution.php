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

use whatwedo\CronBundle\CronJob\CronJobInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Execution
 *
 * @package whatwedo\CronBundle\Entity
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
    protected $class;

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
        $this->startedAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     *
     * @return self
     */
    public function setState(?string $state): self
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    /**
     * @param string|null $class
     *
     * @return self
     */
    public function setClass(?string $class): self
    {
        $this->class = $class;
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
     *
     * @return self
     */
    public function setCommand(?array $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    /**
     * @param \DateTime|null $startedAt
     *
     * @return self
     */
    public function setStartedAt(?\DateTime $startedAt): self
    {
        $this->startedAt = $startedAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     *
     * @return self
     */
    public function setUpdatedAt(?\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getFinishedAt(): ?\DateTime
    {
        return $this->finishedAt;
    }

    /**
     * @param \DateTime|null $finishedAt
     *
     * @return self
     */
    public function setFinishedAt(?\DateTime $finishedAt): self
    {
        $this->finishedAt = $finishedAt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPid(): ?int
    {
        return $this->pid;
    }

    /**
     * @param int|null $pid
     *
     * @return self
     */
    public function setPid(?int $pid): self
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }

    /**
     * @param int|null $exitCode
     *
     * @return self
     */
    public function setExitCode(?int $exitCode): self
    {
        $this->exitCode = $exitCode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStdout(): ?string
    {
        return $this->stdout;
    }

    /**
     * @param string|null $stdout
     *
     * @return self
     */
    public function setStdout(?string $stdout): self
    {
        $this->stdout = $stdout;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getStderr(): ?string
    {
        return $this->stderr;
    }

    /**
     * @param string|null $stderr
     *
     * @return self
     */
    public function setStderr(?string $stderr): self
    {
        $this->stderr = $stderr;
        return $this;
    }

    /**
     * @return CronJobInterface|null
     */
    public function getCronJob(): ?CronJobInterface
    {
        return $this->cronJob;
    }

    /**
     * @param CronJobInterface|null $cronJob
     *
     * @return self
     */
    public function setCronJob(?CronJobInterface $cronJob): self
    {
        $this->cronJob = $cronJob;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return '#'.str_pad($this->getId(), 6, '0', STR_PAD_LEFT);
    }
}

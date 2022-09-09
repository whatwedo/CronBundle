<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Browser;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Tests\App\CronJob\ListCron;
use Zenstruck\Browser\Test\HasBrowser;

class ControllerTest extends KernelTestCase
{
    use HasBrowser;

    public function testIndex(): void
    {
        $this->browser()
            ->visit('/index')
            ->assertSuccessful()
        ;
    }

    public function testShow(): void
    {
        $this->browser()
            ->visit('/show/' . ListCron::class)
            ->assertSuccessful()
        ;
    }

    public function testExecution(): void
    {
        $execution = new Execution();
        $execution->setState(Execution::STATE_FINISHED);
        $execution->setJob(ListCron::class);
        $execution->setExitCode(0);

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $entityManager->persist($execution);
        $entityManager->flush();

        $this->browser()
            ->visit('/excecution/' . $execution->getId())
            ->assertSuccessful()
        ;
    }
}

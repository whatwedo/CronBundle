<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Browser;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use whatwedo\CronBundle\Entity\Execution;
use whatwedo\CronBundle\Tests\App\CronJob\DemoCron;
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
            ->visit('/show/'.urlencode(DemoCron::class))
            ->assertSuccessful()
        ;
    }

    public function testExecution(): void
    {
        $execution = new Execution();
        $execution->setState(Execution::STATE_FINISHED);
        $execution->setJob(DemoCron::class);
        $execution->setExitCode(0);

        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);
        $em->persist($execution);
        $em->flush();

        $this->browser()
            ->visit('/excecution/'.$execution->getId())
            ->assertSuccessful()
        ;
    }
}

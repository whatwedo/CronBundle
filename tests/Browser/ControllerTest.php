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

    public function testIndex()
    {
        $this->browser()
            ->visit('/index')
            ->assertSuccessful()
        ;
    }

    public function testShow()
    {
        $this->browser()
            ->visit('/show/' . DemoCron::class)
            ->assertSuccessful()
        ;
    }

    public function testExecution()
    {
        $execution = new Execution();
        $execution->setState(Execution::STATE_FINISHED);
        $execution->setJob(DemoCron::class);
        $execution->setExitCode(0);

        self::getContainer()->get(EntityManagerInterface::class)->persist($execution);
        self::getContainer()->get(EntityManagerInterface::class)->flush();

        $this->browser()
            ->visit('/excecution/' . $execution->getId())
            ->assertSuccessful()
        ;
    }
}

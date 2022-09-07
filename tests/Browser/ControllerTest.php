<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\Browser;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;

class ControllerTest extends KernelTestCase
{
    use HasBrowser;

    public function testController()
    {
        $this->browser()
            ->visit('/wwd_cronjob')

            ->assertSuccessful()
        ;
    }
}

<?php

namespace whatwedo\CronBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class CronExtension extends AbstractExtension   
{
    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('wwd_cron_classFqcn', fn ($object) => $object::class),
        ];
    }
}
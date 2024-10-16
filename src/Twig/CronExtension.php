<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Twig;

use Twig\Extension\AbstractExtension;

class CronExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('wwd_cron_classFqcn', fn ($object) => $object::class),
        ];
    }
}

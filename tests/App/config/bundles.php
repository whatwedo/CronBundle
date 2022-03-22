<?php

declare(strict_types=1);

return [
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => [
        'all' => true,
    ],
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => [
        'all' => true,
    ],
    Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => [
        'all' => true,
    ],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => [
        'all' => true,
    ],
    Symfony\Bundle\TwigBundle\TwigBundle::class => [
        'all' => true,
    ],
    Zenstruck\Foundry\ZenstruckFoundryBundle::class => [
        'all' => true,
    ],
    Symfony\Bundle\MakerBundle\MakerBundle::class => [
        'all' => true,
    ],
];

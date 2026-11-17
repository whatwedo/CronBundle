<?php

declare(strict_types=1);

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container): void {
    // ORM 2.x only: opt into the ORM 3 behaviour to avoid deprecations.
    // These options are mandatory with ORM 3 and removed in DoctrineBundle 3.
    if (! class_exists(AnnotationDriver::class)) {
        return;
    }

    $container->extension('doctrine', [
        'orm' => [
            'enable_lazy_ghost_objects' => true,
            'report_fields_where_declared' => true,
        ],
    ]);
};

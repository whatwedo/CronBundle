<?php

declare(strict_types=1);

use Doctrine\Deprecations\Deprecation;
use Symfony\Component\Filesystem\Filesystem;

require \dirname(__DIR__).'/vendor/autoload.php';

// DoctrineBundle 2.x (the newest release on PHP 8.3) still calls Type::hasType(), deprecated in DBAL 4.6.
Deprecation::ignoreDeprecations('https://github.com/doctrine/dbal/pull/7342');

(new Filesystem())->remove(__DIR__.'/../var');

shell_exec('tests/App/bin/console doctrine:database:drop --if-exists --force -n --env=test');
shell_exec('tests/App/bin/console doctrine:database:create -n --env=test');
shell_exec('tests/App/bin/console doctrine:schema:create --env=test');

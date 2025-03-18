<?php

declare(strict_types=1);

use Symfony\Component\Filesystem\Filesystem;

require \dirname(__DIR__).'/vendor/autoload.php';

(new Filesystem())->remove(__DIR__.'/../var');

shell_exec('tests/App/bin/console doctrine:database:drop --if-exists --force -n --env=test');
shell_exec('tests/App/bin/console doctrine:database:create -n --env=test');
shell_exec('tests/App/bin/console doctrine:schema:create --env=test');

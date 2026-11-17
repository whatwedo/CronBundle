# Getting started

This documentation provides a basic view of the possibilities of the whatwedoCronBundle. The documentation will be extended while developing the bundle.

## Requirements

This bundle supports PHP >= 8.3, Symfony 6.4, 7.x and 8.x, Doctrine ORM >= 2.13 and DoctrineBundle >= 2.8. It is tested against Symfony 6.4 with Doctrine ORM 2, Symfony 7.4 and Symfony 8 (PHP 8.3 to 8.5).

## Upgrading

### To 1.1 (Symfony 8 support)

- The date columns of `whatwedo_cron_execution` (`started_at`, `updated_at`, `finished_at`) are now mapped as `datetime_immutable`. The SQL type does not change. On DBAL 4 no migration is needed. On DBAL 3 (Doctrine ORM 2) `doctrine:migrations:diff` generates a migration that only adds the `(DC2Type:datetime_immutable)` column comments.
- `Execution` getters still return `\DateTime`, but as copies: modifying a returned date no longer changes the entity. Use the setters, which now accept any `\DateTimeInterface`.
- The Doctrine listener is registered via `#[AsDoctrineListener]`, so DoctrineBundle >= 2.8 is required.
- `whatwedo_cron_execution` gets new indexes for all queried columns, replacing the `started_at` index. Generate and run a migration (`doctrine:migrations:diff`).
- `whatwedo:cron:cleanup` now also deletes stale executions, by default those older than one month (`--max-retention-stale`).
- `ExecutionRepository::findNonPendingExcecution()`, removed in 1.0.0, is available again.

## Installation

First, add the bundle to your dependencies and install it.

```bash
composer install whatwedo/cron-bundle
```

Secondly, enable this bundle in your kernel. (if you are using Symfony Flex this is done automatically)

```php
<?php
// config/bundles.php

return [
    // ...
    whatwedo\CronBundle\whatwedoCronBundle::class => ['all' => true],
    // ...
];
```

## Use the bundle

### Create a CronJob

There are 2 ways to create a CronJob.

#### Implementing interface on command

By implementing the [CronInterface](../../CronJob/CronInterface.php) interface on a command.

**Example:**

```php
<?php
// src/Command/MyCommand.php

namespace whatwedo\CronBundle\Tests\App\Command;

use Symfony\Component\Console\Command\Command;
// ...
use whatwedo\CronBundle\CronJob\CronInterface;

/**
 * Class TestCommand
 *
 * @package whatwedo\CronBundle\Command
 */
class MyCommand extends Command implements CronInterface
{
    // ...

    public function getExpression(): string
    {
        return '15 13 * * *'; // Execute daily at 13:15 
    }

    public function getMaxRuntime(): ?int
    {
        return null; // Max runtime in seconds or null for unlimited
    }

    public function isParallelAllowed(): bool
    {
        return true; // Is parallel execution allowed 
    }

    public function isActive(): bool
    {
        return true; // Is the cron active?
    }
}
```

#### Creating a CronJob class

By creating a CronJob class and extending the [AbstractCronJob](../../CronJob/AbstractCronJob.php).

**Example:**

```php
<?php
// src/CronJob/MyCronJob.php

namespace whatwedo\CronBundle\Tests\App\CronJob;

use whatwedo\CronBundle\CronJob\AbstractCronJob;

class MyCronJob extends AbstractCronJob
{
    public function getCommand(): string
    {
        return 'app:my-command'; // Symfony command to run
    }

    public function getExpression(): string
    {
        return '*/5 * * * * '; // Will run every 5 minutes
    }
}
```

### Run CronJobs

#### Automatic run

The command `php bin/console whatwedo:cron:scheduler` checks every 15 seconds for CronJobs to be run.

The command has an optional parameter `--max-runtime [seconds]` that defines how long the command may run. The default value is 600 seconds.

> It is recommended to use a service like `supervise` or other services to ensure the command restarts after it exits.

#### Manual CronJob execution

For a one off execution of a CronJob use the Command `php bin/console whatwedo:cron:execute [CronJob]`.

> The one of command does not check the CronJob expression. The CronJob will run like a Symfony command.

## Events

The bundle provides 3 events, that will be triggered in different stages of the CronJob execution.

| event                  | stage                                     |
| ---------------------- | ----------------------------------------- |
| whatwedo.cron.start    | CronJob execution started                 |
| whatwedo.cron.finished | CronJob execution finished                |
| whatwedo.cron.error    | An error occurred while CronJob execution |

# Getting started

This documentation provides a basic view of the possibilities of the whatwedoCronBundle. The documentation will be extended while developing the bundle.

## Requirements

This bundle has been tested on PHP >= 7.3 and Symfony >= 4.2. We don't guarantee that it works on lower versions.

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

namespace App\Command;

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

namespace App\CronJob;

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

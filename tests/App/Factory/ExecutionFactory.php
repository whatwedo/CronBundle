<?php

declare(strict_types=1);

namespace whatwedo\CronBundle\Tests\App\Factory;

use whatwedo\CronBundle\Entity\Execution;
use Zenstruck\Foundry\ModelFactory;

class ExecutionFactory extends ModelFactory
{
    protected static function getClass(): string
    {
        return Execution::class;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getDefaults(): array
    {
        return [
            'startedAt' => self::faker()->dateTimeBetween('-1 year', '-1 day'),
        ];
    }

    protected function initialize(): self
    {
        $this->beforeInstantiate(function (array $attributes): array {
            return $attributes;
        });

        return $this;
    }
}

<?php

namespace App\Dto\Task\Filters;

use App\Dto\Task\Filters\Contracts\TaskFilterInterface;

class TaskStringFilter implements TaskFilterInterface
{
    public function __construct(
        private readonly string $key,
        private readonly string $value
    )
    {
    }

    public function type(): TaskFilterType
    {
        return TaskFilterType::STRING;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}

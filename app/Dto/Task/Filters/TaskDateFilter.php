<?php

namespace App\Dto\Task\Filters;

use App\Dto\Task\Filters\Contracts\TaskFilterInterface;
use Illuminate\Support\Carbon;

class TaskDateFilter implements TaskFilterInterface
{
    public function __construct(
        private readonly string $key,
        private readonly Carbon $value
    )
    {
    }

    public function type(): TaskFilterType
    {
        return TaskFilterType::DATE;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): Carbon
    {
        return $this->value;
    }
}

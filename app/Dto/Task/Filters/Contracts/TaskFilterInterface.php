<?php

namespace App\Dto\Task\Filters\Contracts;

use App\Dto\Task\Filters\TaskFilterType;

interface TaskFilterInterface
{
    public function type(): TaskFilterType;
    public function key(): string;
    public function value(): mixed;
}

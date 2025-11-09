<?php

namespace App\Dto\Task;

final class TaskCreationRequest
{
    public function __construct(
        public string $title,
        public string $description,
        public string $status,
        public int $assignee_id,
        public int $project_id,
        public string $completion_date,
    ) {}
}

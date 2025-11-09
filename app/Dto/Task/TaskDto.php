<?php

namespace App\Dto\Task;

use App\Dto\Project\ProjectDto;
use App\Dto\User\UserDto;

class TaskDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $status,
        public UserDto $assignee,
        public ProjectDto $project,
        public array $attachments,
        public ?string $completion_date,
        public string $created_at,
        public string $updated_at,
    )
    {
    }
}

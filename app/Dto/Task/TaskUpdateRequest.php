<?php

namespace App\Dto\Task;

class TaskUpdateRequest
{
    private ?string $title = null;
    private ?string $description = null;
    private ?string $status = null;
    private ?string $completion_date = null;
    private ?string $assignee_id = null;

    public function __construct(array $data = [])
    {
        foreach (array_keys(get_class_vars(self::class)) as $key) {
            $this->$key = $data[$key] ?? null;
        }
    }

    public function getUpdates(): array {
        return array_filter(get_object_vars($this), fn($v) => !is_null($v));
    }
}

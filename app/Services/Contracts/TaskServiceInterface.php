<?php

namespace App\Services\Contracts;

use App\Dto\Task\TaskCreationRequest;
use App\Dto\Task\TaskDto;
use App\Dto\Task\TaskUpdateRequest;

interface TaskServiceInterface
{
    public function getTasksByProject(int $projectId, array $filters): array;
    public function getTask(int $id): TaskDto;
    public function createTask(int $projectId, TaskCreationRequest $task): TaskDto;
    public function updateTask(int $id, TaskUpdateRequest $task): void;
    public function deleteTask(int $id): void;
    public function attachFile(int $id, $file): void;
    public function detachFile(int $id, int $mediaId): void;
}

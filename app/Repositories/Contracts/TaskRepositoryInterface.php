<?php

namespace App\Repositories\Contracts;

use App\Dto\Task\TaskCreationRequest;
use App\Dto\Task\TaskDto;
use App\Dto\Task\TaskUpdateRequest;

interface TaskRepositoryInterface
{
    public function getById(int $id): TaskDto;
    public function getByProject(int $projectId, array $filters = []): array;

    public function create(TaskCreationRequest $creationRequest): TaskDto;

    public function update(int $id, TaskUpdateRequest $update): void;

    public function delete(int $id): void;

    public function attachFile(int $id, $file): void;

    public function detachFile(int $id, int $mediaId): void;
}

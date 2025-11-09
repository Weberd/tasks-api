<?php

namespace App\Services;

use App\Dto\Task\TaskCreationRequest;
use App\Dto\Task\TaskDto;
use App\Dto\Task\TaskUpdateRequest;
use App\Jobs\SendTaskCreatedNotification;
use App\Repositories\Contracts\ProjectRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\Contracts\TaskServiceInterface;

final readonly class TaskService implements TaskServiceInterface
{
    public function __construct(
        private TaskRepositoryInterface    $taskRepository,
        private ProjectRepositoryInterface $projectRepository
    )
    {
    }

    public function getTasksByProject(int $projectId, array $filters): array
    {
        return $this->taskRepository->getByProject($projectId, $filters);
    }

    public function getTask(int $id): TaskDto
    {
        return $this->taskRepository->getById($id);
    }

    public function createTask(int $projectId, TaskCreationRequest $task): TaskDto
    {
        if (!$this->projectRepository->projectExists($projectId)) {
            throw new NotFoundHttpException('Project not found');
        }

        $task->project_id = $projectId;
        $result = $this->taskRepository->create($task);

        SendTaskCreatedNotification::dispatch($result->id);
        return $result;
    }

    public function updateTask(int $id, TaskUpdateRequest $task): void
    {
        $this->taskRepository->update($id, $task);
    }

    public function deleteTask(int $id): void
    {
        $this->taskRepository->delete($id);
    }

    public function attachFile(int $id, $file): void
    {
        $this->taskRepository->attachFile($id, $file);
    }

    public function detachFile(int $id, int $mediaId): void
    {
        $this->taskRepository->detachFile($id, $mediaId);
    }
}

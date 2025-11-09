<?php

namespace App\Repositories;

use App\Dto\Media\MediaDto;
use App\Dto\Project\ProjectDto;
use App\Dto\Task\Filters\TaskDateFilter;
use App\Dto\Task\Filters\TaskFilterType;
use App\Dto\Task\TaskCreationRequest;
use App\Dto\Task\TaskDto;
use App\Dto\Task\TaskUpdateRequest;
use App\Dto\User\UserDto;
use App\Models\Task;
use App\Repositories\Contracts\TaskRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class TaskRepository implements TaskRepositoryInterface
{
    private const MAX_DEADLOCK_RETRIES = 5;

    public function __construct(
        private readonly Task $model
    ) {}

    public function getById(int $id): TaskDto
    {
        $task = $this->model
            ->with(['project', 'assignee', 'media'])
            ->findOrFail($id);

        return $this->model2Dto($task);
    }

    public function getByProject(int $projectId, array $filters = []): array
    {
        return DB::transaction(function () use ($projectId, $filters) {
            $query = $this->model
                ->where('project_id', $projectId)
                ->with(['project', 'assignee', 'media']);

            // Применить фильтры
            foreach ($filters as $filter) {
                match ($filter->type()) {
                    TaskFilterType::STRING => $query->where($filter->key(), $filter->value()),
                    TaskFilterType::DATE => $query->whereDate($filter->key(), $filter->value()),
                };
            }

            $result = [];

            $query->chunk(100, function (Collection $tasks) use (&$result) {
                foreach ($tasks as $task) {
                    $result[] = $this->model2Dto($task);
                }
            });

            return $result;
        }, self::MAX_DEADLOCK_RETRIES);
    }

    public function create(TaskCreationRequest $creationRequest): TaskDto
    {
        $task = $this->model->create((array)$creationRequest);
        return $this->model2Dto($task);
    }

    public function update(int $id, TaskUpdateRequest $update): void
    {
        DB::transaction(function () use ($id, $update) {
            $task = Task::where('id', $id)->lockForUpdate()->firstOrFail();
            $task->update($update->getUpdates());
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $task = Task::where('id', $id)->lockForUpdate()->firstOrFail();
            $task->delete();
        });
    }

    public function attachFile(int $id, $file): void
    {
        DB::transaction(function () use ($id, $file) {
            $task = Task::where('id', $id)->lockForUpdate()->firstOrFail();
            $task->addMedia($file)->toMediaCollection('attachments');
            $task->load('media');
        });
    }

    public function detachFile(int $id, int $mediaId): void
    {
        DB::transaction(function () use ($id, $mediaId) {
            $task = Task::where('id', $id)->lockForUpdate()->firstOrFail();
            $media = $task->getMedia('attachments')->find($mediaId);
            $media?->delete();
        });
    }

    protected function model2Dto(Task $task): TaskDto
    {
        return new TaskDto(
            $task->id,
            $task->title,
            $task->description,
            $task->status,
            new UserDto($task->assignee->name, $task->assignee->email),
            new ProjectDto($task->project->name, $task->project->description),
            $task->media->map(function ($media) {
                return new MediaDto($media->id, $media->file_name, $media->mime_type, $media->size);
            })->toArray(),
            $task->completion_date,
            $task->created_at,
            $task->updated_at,
        );
    }
}

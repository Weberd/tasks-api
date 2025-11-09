<?php

namespace App\Http\Controllers\Api;

use App\Dto\Task\Filters\TaskDateFilter;
use App\Dto\Task\Filters\TaskStringFilter;
use App\Dto\Task\TaskCreationRequest;
use App\Dto\Task\TaskUpdateRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetTasksRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Services\TaskService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function __construct(
        protected TaskService $taskService
    ) {}

    /**
     * Получение списка задач по проекту с фильтрацией
     */
    public function index(int $projectId, GetTasksRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $filters = [];

            foreach ($validated as $key => $value) {
                if ($key === 'completion_date') {
                    $filters[] = new TaskDateFilter($key, $value);
                } else {
                    $filters[] = new TaskStringFilter($key, $value);
                }
            }

            $tasks = $this->taskService->getTasksByProject($projectId, $filters);

            return response()->json([
                'success' => true,
                'data' => $tasks,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Создание новой задачи
     */
    public function store(int $projectId, StoreTaskRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $attachment = $request->file('attachment');

            // Удаляем attachment из данных, т.к. он обрабатывается отдельно
            unset($data['attachment']);

            $creationRequest = new TaskCreationRequest(
                $data['title'],
                $data['description'],
                $data['status'],
                $data['assignee_id'],
                $projectId,
                $data['completion_date'],
            );

            $task = $this->taskService->createTask($projectId, $creationRequest);

            // Прикрепляем файл, если он есть
            if ($attachment) {
                $this->taskService->attachFile($task->id, $attachment);
            }

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => new TaskResource($this->taskService->getTask($task->id)),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Получение информации о задаче
     */
    public function show(int $id): JsonResponse
    {
        try {
            $task = $this->taskService->getTask($id);

            return response()->json([
                'success' => true,
                'data' => new TaskResource($task),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], $e->getCode() ?: 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Обновление данных задачи
     */
    public function update(int $id, UpdateTaskRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $attachment = $request->file('attachment');

            // Удаляем attachment из данных
            unset($validated['attachment']);

            $this->taskService->updateTask($id, new TaskUpdateRequest($validated));

            // Прикрепляем новый файл, если он есть
            if ($attachment) {
                $this->taskService->attachFile($id, $attachment);
            }

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => new TaskResource($this->taskService->getTask($id)),
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Удаление задачи
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->taskService->deleteTask($id);

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Task not found',
            ], $e->getCode() ?: 404);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
            ], $e->getCode() ?: 500);
        }
    }
}

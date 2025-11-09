<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'api_token' => Str::random(80),
        ]);

        $this->project = Project::factory()->create();
    }

    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_get_tasks(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'assignee_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->user->api_token)
            ->getJson("/api/projects/{$this->project->id}/tasks");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'status',
                        'completion_date',
                        'project',
                        'assignee',
                        'attachments',
                        'created_at',
                        'updated_at'
                    ],
                ],
            ]);
    }

    public function test_can_filter_tasks_by_status(): void
    {
        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'planned',
        ]);

        Task::factory()->create([
            'project_id' => $this->project->id,
            'status' => 'in_progress',
        ]);

        $response = $this->withToken($this->user->api_token)
            ->getJson("/api/projects/{$this->project->id}/tasks?status=planned");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('planned', $data[0]['status']);
    }

    public function test_can_create_task(): void
    {
        Storage::fake('public');

        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'planned',
            'completion_date' => now()->format('Y-m-d'),
            'assignee_id' => $this->user->id,
            'attachment' => UploadedFile::fake()->create('document.pdf', 100), // 100 KB,
        ];

        $response = $this->withToken($this->user->api_token)
            ->postJson("/api/projects/{$this->project->id}/tasks", $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'title',
                    'description',
                    'status',
                    'attachments'
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'project_id' => $this->project->id,
        ]);
    }

    public function test_create_task_validation_fails_with_invalid_status(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'status' => 'invalid_status',
        ];

        $response = $this->withToken($this->user->api_token)
            ->postJson("/api/projects/{$this->project->id}/tasks", $taskData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_can_update_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ];

        $response = $this->withToken($this->user->api_token)
            ->putJson("/api/tasks/{$task->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully',
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'in_progress',
        ]);
    }

    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
        ]);

        $response = $this->withToken($this->user->api_token)
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully',
            ]);

        $response = $this->withToken($this->user->api_token)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found',
            ]);
    }

    public function test_can_get_single_task(): void
    {
        $task = Task::factory()->create([
            'project_id' => $this->project->id,
            'assignee_id' => $this->user->id,
        ]);

        $response = $this->withToken($this->user->api_token)
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $task->status,
                    'completion_date' => $task->completion_date?->toISOString(),
                    'project' => [
                        'name' => $task->project->name,
                        'description' => $task->project->description,
                    ],
                    'assignee' => [
                        'name' => $task->assignee->name,
                        'email' => $task->assignee->email,
                    ],
                    'attachments' => $task->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'file_name' => $media->file_name,
                            'mime_type' => $media->mime_type,
                            'size' => $media->size,
                        ];
                    })->toArray(),
                    'created_at' => $task->created_at,
                    'updated_at' => $task->updated_at,
                ],
            ]);
    }

    public function test_returns_404_for_nonexistent_task(): void
    {
        $response = $this->withToken($this->user->api_token)
            ->getJson('/api/tasks/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found',
            ]);
    }
}

<?php

namespace App\Jobs;

use App\Models\Task;
use App\Notifications\TaskCreatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTaskCreatedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $taskId
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $task = Task::query()->findOrFail($this->taskId);

        if ($task->assignee) {
            $task->assignee->notify(new TaskCreatedNotification($task));
        }
    }
}

<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TaskCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Task $task
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Task Assigned: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('You have been assigned a new task.')
            ->line('Task: ' . $this->task->title)
            ->line('Project: ' . $this->task->project->name)
            ->line('Description: ' . ($this->task->description ?? 'No description'))
            ->line('Status: ' . $this->task->status)
            ->when($this->task->completion_date, function ($mail) {
                return $mail->line('Due Date: ' . $this->task->completion_date->format('Y-m-d'));
            })
            ->action('View Task', url('/tasks/' . $this->task->id));
    }
}

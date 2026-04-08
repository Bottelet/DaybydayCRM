<?php

namespace App\Events;

use App\Models\Task;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class TaskAction
{
    private $task;

    private $action;

    use InteractsWithSockets, SerializesModels;

    public function getTask()
    {
        return $this->task;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * Create a new event instance.
     * TaskAction constructor.
     */
    public function __construct(Task $task, $action)
    {
        $this->task = $task;
        $this->action = $action;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}

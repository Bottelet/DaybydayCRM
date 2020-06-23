<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Task;

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
     * @param Task $task
     * @param $action
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

<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Tasks;

class TaskAction
{
    private $task;
    private $action;
    private $text;

    use InteractsWithSockets, SerializesModels;

    public function getTask()
    {
        return $this->task;
    }
    public function getAction()
    {
        return $this->action;
    }
    public function getText()
    {   
        return $this->text;
    }

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Tasks $task, $action, $text)
    {
        $this->task = $task;
        $this->action = $action;
        $this->text = $text;
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

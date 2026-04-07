<?php

namespace App\Events;

use App\Models\Project;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class ProjectAction
{
    private $project;

    private $action;

    use InteractsWithSockets, SerializesModels;

    public function getProject()
    {
        return $this->project;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * Create a new event instance.
     * projectAction constructor.
     */
    public function __construct(Project $project, $action)
    {
        $this->project = $project;
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

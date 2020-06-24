<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Project;

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
     * @param project $project
     * @param $action
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

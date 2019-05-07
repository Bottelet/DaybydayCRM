<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Models\Lead;

class LeadAction
{
    private $lead;
    private $action;

    use InteractsWithSockets;
    use SerializesModels;

    public function getLead()
    {
        return $this->lead;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * Create a new event instance.
     * LeadAction constructor.
     *
     * @param Lead $lead
     * @param $action
     */
    public function __construct(Lead $lead, $action)
    {
        $this->lead   = $lead;
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

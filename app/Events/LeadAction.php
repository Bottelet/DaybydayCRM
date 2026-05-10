<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class LeadAction
{
    use InteractsWithSockets;
    use SerializesModels;

    private $lead;

    private $action;

    /**
     * Create a new event instance.
     * LeadAction constructor.
     */
    public function __construct(Lead $lead, $action)
    {
        $this->lead   = $lead;
        $this->action = $action;
    }

    public function getLead()
    {
        return $this->lead;
    }

    public function getAction()
    {
        return $this->action;
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

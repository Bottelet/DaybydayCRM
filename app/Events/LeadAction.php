<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Leads;

class LeadAction
{
    private $lead;
    private $action;

    use InteractsWithSockets, SerializesModels;

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
     *
     * @return void
     */
    public function __construct(Leads $lead, $action)
    {
        $this->lead = $lead;
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

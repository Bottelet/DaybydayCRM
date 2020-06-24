<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use App\Models\Client;

class ClientAction
{
    private $client;
    private $action;

    use InteractsWithSockets, SerializesModels;

    public function getClient()
    {
        return $this->client;
    }
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Create a new event instance.
     * ClientAction constructor.
     * @param Client $client
     * @param $action
     */
    public function __construct(Client $client, $action)
    {
        $this->client = $client;
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

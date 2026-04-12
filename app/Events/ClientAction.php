<?php

namespace App\Events;

use App\Models\Client;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;

class ClientAction
{
    use InteractsWithSockets;
    use SerializesModels;

    private $client;

    private $action;

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

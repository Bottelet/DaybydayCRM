<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use App\Models\Contact;

class ContactAction
{
    private $contact;
    private $action;

    use InteractsWithSockets;
    use SerializesModels;

    public function getContact()
    {
        return $this->contact;
    }

    public function getAction()
    {
        return $this->action;
    }

    /**
     * Create a new event instance.
     * ContactAction constructor.
     *
     * @param Contact $contact
     * @param $action
     */
    public function __construct(Contact $contact, $action)
    {
        $this->contact = $contact;
        $this->action  = $action;
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

<?php

namespace App\Listeners;

use App\Events\ClientAction;
use App\Notifications\ClientActionNotification;

class ClientActionNotify
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(ClientAction $event)
    {
        $client = $event->getClient();
        $action = $event->getAction();

        $client->assignedUser->notify(new ClientActionNotification(
            $client,
            $action
        ));
    }
}

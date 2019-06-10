<?php

namespace App\Listeners;

use App\Events\ContactAction;
use App\Notifications\ContactActionNotification;

class ContactActionNotify
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     *
     * @param ContactAction $event
     */
    public function handle(ContactAction $event)
    {
        $contact = $event->getContact();
        $action  = $event->getAction();

        $contact->assignedClient->notify(new ContactActionNotification(
            $contact,
            $action
        ));
    }
}

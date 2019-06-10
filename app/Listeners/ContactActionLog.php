<?php

namespace App\Listeners;

use App\Events\ContactAction;
use App\Models\Activity;
use App\Models\Contact;

class ContactActionLog
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

        switch ($event->getAction()) {
            case 'created':
                $text = __('Contact :company was assigned to :assignee', [
                    'name'     => $contact->name,
                    'assignee' => $contact->AssignedClient->name,
                ]);
                break;
            default:
                break;
        }

        $activityinput = array_merge(
            [
                'text'        => $text,
                'user_id'     => Auth()->id(),
                'source_type' => Contact::class,
                'source_id'   => $contact->id,
                'action'      => $event->getAction(),
            ]
        );

        Activity::create($activityinput);
    }
}

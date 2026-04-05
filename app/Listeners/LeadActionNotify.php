<?php

namespace App\Listeners;

use App\Events\LeadAction;
use App\Notifications\LeadActionNotification;

class LeadActionNotify
{
    /**
     * Action the event listener.
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
    public function handle(LeadAction $event)
    {
        $lead = $event->getLead();
        $action = $event->getAction();
        $lead->user->notify(new LeadActionNotification(
            $lead,
            $action
        ));
    }
}

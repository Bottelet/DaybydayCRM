<?php

namespace App\Listeners;

use App\Events\LeadAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\LeadActionNotification;

class LeadActionNotify
{
    /**
     * Action the event listener.
     *
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LeadAction  $event
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

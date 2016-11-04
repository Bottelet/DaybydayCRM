<?php

namespace App\Listeners;

use App\Events\LeadCreate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LeadCreateLog
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  LeadCreate  $event
     * @return void
     */
    public function handle(LeadCreate $event)
    {
        //
    }
}

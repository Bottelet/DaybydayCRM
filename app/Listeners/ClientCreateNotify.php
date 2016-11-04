<?php

namespace App\Listeners;

use App\Events\ClientCreate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ClientCreateNotify
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
     * @param  ClientCreate  $event
     * @return void
     */
    public function handle(ClientCreate $event)
    {
        //
    }
}

<?php

namespace App\Listeners;

use App\Events\ClientCreate;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use App\Models\Client;

class ClientCreateLog
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
        $client = $event->getClient();
        $activityinput = array_merge(
            [
                'text' => 'Client ' . $client->company_name .
                ' was assigned to '. $client->AssignedUser->name,
                'user_id' => Auth()->id(),
                'type' => Client::class,
                'type_id' =>  $client->id
            ]);
        
        Activity::create($activityinput);
    }
}

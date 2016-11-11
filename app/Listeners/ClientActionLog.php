<?php

namespace App\Listeners;

use App\Events\ClientAction;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Activity;
use App\Models\Client;

class ClientActionLog
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
     * @param  ClientAction  $event
     * @return void
     */
    public function handle(ClientAction $event)
    {
        $client = $event->getClient();

        switch ($event->getAction()) {
            case 'created':
                $text = 'Client ' . $client->company_name .
                ' was assigned to '. $client->AssignedUser->name;
                break;
            default:
                break;
        }
    
        $activityinput = array_merge(
            [
                'text' => $text,
                'user_id' => Auth()->id(),
                'type' => Client::class,
                'type_id' =>  $client->id,
                'action' => $event->getAction()
            ]);
        
        Activity::create($activityinput);
    }
}

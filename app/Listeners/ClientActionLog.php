<?php

namespace App\Listeners;

use App\Events\ClientAction;

class ClientActionLog
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

        switch ($event->getAction()) {
            case 'created':
                $text = __('Client :company was assigned to :assignee', [
                    'company' => $client->company_name,
                    'assignee' => $client->AssignedUser->name,
                ]);
                break;
            case 'updated_assign':
                $text = __(':username assigned client to :assignee', [
                    'username' => Auth()->user()->name,
                    'assignee' => $client->AssignedUser->name,
                ]);
                break;
            default:
                break;
        }

        activity('client')
            ->performedOn($client)
            ->withProperties(['action' => $event->getAction()])
            ->log($text);
    }
}

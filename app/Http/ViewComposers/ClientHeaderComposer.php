<?php

namespace App\Http\ViewComposers;

use App\Models\Client;
use Illuminate\View\View;

class ClientHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        // Re-use the already eager-loaded client from the view data to prevent N+1 queries.
        // getClientWithRelations() in ClientsController pre-loads 'user' and 'primaryContact'.
        $client = $view->getData()['client'];

        $contact_info = $client->relationLoaded('primaryContact')
            ? $client->primaryContact
            : $client->contacts->first();

        $contact = $client->relationLoaded('user')
            ? $client->user
            : $client->user()->first();

        $view->with('contact', $contact)->with('contact_info', $contact_info);
    }
}

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
        // Eager load relationships to prevent N+1 queries
        $clients = Client::with(['contacts', 'user'])
            ->findOrFail($view->getData()['client']['id']);

        // Use the already eager-loaded contacts collection instead of querying again
        $contact_info = $clients->contacts->first();
        /**
         * [User assigned the client].
         *
         * @var contact
         */
        $contact = $clients->user;

        $view->with('contact', $contact)->with('contact_info', $contact_info);
    }
}

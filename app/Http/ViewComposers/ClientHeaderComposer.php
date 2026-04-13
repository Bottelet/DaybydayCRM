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
        $clients = Client::findOrFail($view->getData()['client']['id']);

        $contact_info = $clients->contacts()->first();
        /**
         * [User assigned the client].
         *
         * @var contact
         */
        $contact = $clients->user;

        $view->with('contact', $contact)->with('contact_info', $contact_info);
    }
}

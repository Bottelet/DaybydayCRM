<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Lead;

class LeadHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $lead = Lead::findOrFail($view->getData()['lead']['id']);
        /**
         * [User assigned the task]
         * @var contact
         */
       
        $contact = $lead->user;
        $client = $lead->client;
        $contact_info = $client->contacts()->first();

        $view->with('contact', $contact);
        $view->with('contact_info', $contact_info);
        $view->with('client', $client);
    }
}

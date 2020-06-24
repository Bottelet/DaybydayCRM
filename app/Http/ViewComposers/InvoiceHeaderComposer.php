<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use App\Models\Invoice;

class InvoiceHeaderComposer
{


    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $invoices = Invoice::findOrFail($view->getData()['invoice']['id']);

        $client = $invoices->client;
        $contact_info = $client->contacts()->first();

        $view->with('client', $client);
        $view->with('contact_info', $contact_info);
    }
}

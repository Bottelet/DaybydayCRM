<?php

namespace App\Http\ViewComposers;

use App\Models\Invoice;
use Illuminate\Contracts\View\View;

class InvoiceHeaderComposer
{
    /**
     * Bind data to the view.
     *
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData();

        $invoice = $data['invoice'] ?? null;

        if ($invoice instanceof Invoice) {
            $invoiceModel = $invoice;
        } elseif (is_array($invoice) && isset($invoice['id'])) {
            $invoiceModel = Invoice::find($invoice['id']);
        } else {
            $invoiceModel = null;
        }

        $client       = $invoiceModel?->client;
        $contact_info = $client?->contacts()->first();

        $view->with('client', $client);
        $view->with('contact_info', $contact_info);
    }
}

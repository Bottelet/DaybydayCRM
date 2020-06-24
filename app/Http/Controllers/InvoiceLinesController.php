<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLine;

class InvoiceLinesController extends Controller
{
    public function destroy(InvoiceLine $invoiceLine)
    {
        if (!auth()->user()->can('modify-invoice-lines')) {
            session()->flash('flash_message_warning', __('You do not have permission to modify invoice lines'));
            return redirect()->route('invoices.show', $invoiceLine->invoice->external_id);
        }

        $invoiceLine->delete();

        Session()->flash('flash_message', __('Invoice line successfully deleted'));
        return redirect()->route('invoices.show', $invoiceLine->invoice->external_id);
    }
}

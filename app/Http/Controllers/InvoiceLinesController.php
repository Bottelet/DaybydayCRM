<?php

namespace App\Http\Controllers;

use App\Models\InvoiceLine;

class InvoiceLinesController extends Controller
{
    public function destroy(InvoiceLine $invoiceLine)
    {
        if (! auth()->user()->can('modify-invoice-lines')) {
            session()->flash('flash_message_warning', __('You do not have permission to modify invoice lines'));
            if (request()->expectsJson()) {
                return response()->json(['message' => __('You do not have permission to modify invoice lines')], 403);
            }

            return redirect()->route('invoices.show', $invoiceLine->invoice->external_id);
        }

        $invoiceLine->delete();
        session()->flash('flash_message', __('Invoice line successfully deleted'));

        if (request()->expectsJson()) {
            return response('', 302)->header('X-Redirect', url()->previous() ?: '/');
        }

        return redirect()->route('invoices.show', $invoiceLine->invoice->external_id);
    }
}

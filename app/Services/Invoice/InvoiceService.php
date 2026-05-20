<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\Setting;
use App\Services\Billing\BillingIntegrationRegistry;
use App\Services\Billing\NullBillingAdapter;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Service responsible for billing-related invoice operations.
 *
 * This class encapsulates all interactions with the external billing adapter
 * that were previously embedded in the Invoice Eloquent model. By moving this
 * logic here we:
 *
 *  - Remove integration bootstrapping from model layer.
 *  - Make the billing adapter swappable via DI (easy to fake in tests).
 *  - Give callers a clear, typed API instead of magic static calls.
 */
class InvoiceService
{
    public function __construct(
        private BillingIntegrationRegistry $billing,
        private InvoiceNumberService $invoiceNumbers,
    ) {}

    /**
     * Submit an invoice to the billing back-end (if configured) and return the
     * resulting invoice number and due date.
     *
     * @return array{invoice_number: string|int, due_at: \Carbon\Carbon}
     */
    public function submitToBilling(Invoice $invoice, ?string $contactId): array
    {
        $api = $this->billing->driver();

        if ( ! ($api instanceof NullBillingAdapter) && $contactId) {
            try {
                $setting = Setting::first();
                $results = $api->createInvoice([
                    'currency'            => $setting?->currency ?? 'USD',
                    'show_lines_incl_vat' => true,
                    'description'         => $invoice->source?->title ?? '',
                    'contact_id'          => $contactId,
                    'invoice_lines'       => $invoice->invoiceLines,
                ]);

                if ($results) {
                    $invoice->integration_invoice_id = $results->invoiceId;
                    $invoice->integration_type       = get_class($api);

                    // TODO: Only persist remote IDs after a successful bookInvoice() call.
                    // If bookInvoice() returns falsy below, the invoice is left in a
                    // partially-synced state: it has the remote ID/type persisted but a
                    // locally-generated invoice_number and due date. Consider
                    // book-or-rollback in a future refactor.
                    if ( ! $invoice->save()) {
                        Log::warning('InvoiceService: failed to save integration metadata', [
                            'invoice_id' => $invoice->id,
                        ]);
                    }

                    $booked = $api->bookInvoice($results->invoiceId, $results->timestamp);

                    if ($booked) {
                        return [
                            'invoice_number' => $booked->invoiceNumber,
                            'due_at'         => Carbon::parse($booked->paymentDate),
                        ];
                    }
                }
            } catch (Throwable $e) {
                Log::warning('InvoiceService: billing submission failed', [
                    'invoice_id' => $invoice->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }

        // Fallback: generate invoice number locally.
        return [
            'invoice_number' => $this->invoiceNumbers->nextInvoiceNumber(),
            'due_at'         => Carbon::today()->addDays(14),
        ];
    }

    /**
     * Send an invoice e-mail via the billing adapter.
     */
    public function sendByEmail(
        Invoice $invoice,
        string $subject,
        string $message,
        string $recipient,
        bool $attachPdf = false
    ): bool {
        $api = $this->billing->driver();

        if ($api instanceof NullBillingAdapter) {
            return false;
        }

        try {
            $api->sendInvoice($invoice, $subject, $message, $recipient, $attachPdf);

            activity('task')
                ->performedOn($invoice)
                ->withProperties(['action' => 'sent_invoice'])
                ->log('user has sent the invoice to the customer');

            return true;
        } catch (Throwable $e) {
            Log::warning('InvoiceService: failed to send invoice email', [
                'invoice_id' => $invoice->id,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }
}

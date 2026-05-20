<?php

namespace App\Services\Billing;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Repositories\BillingIntegration\BillingIntegrationInterface;

/**
 * Null object implementation of BillingIntegrationInterface.
 *
 * Used when:
 *  - No billing integration is configured in the database.
 *  - Running in the test environment.
 *  - The configured adapter class cannot be loaded.
 *
 * All methods are no-ops or return safe defaults so that the application
 * continues to function without a real billing back-end.
 */
class NullBillingAdapter implements BillingIntegrationInterface
{
    public function getClient() {}

    public function convertJson($response) {}

    public function createInvoice($params) {}

    public function bookInvoice($invoiceGuid, $timestamp) {}

    public function sendInvoice(Invoice $invoice, $subject, $message, $recipient, $attachPdf = false)
    {
        return false;
    }

    public function getContacts($filter = '')
    {
        return [];
    }

    public function getPrimaryContact(Client $client) {}

    public function getProductMapping(): array
    {
        return [];
    }

    public function createPayment(Payment $payment) {}

    public function deletePayment(Payment $payment)
    {
        return true;
    }
}

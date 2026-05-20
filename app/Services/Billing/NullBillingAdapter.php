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
    public function getClient()
    {
        return null;
    }

    public function convertJson($response)
    {
        return null;
    }

    public function createInvoice($params)
    {
        return null;
    }

    public function bookInvoice($invoiceGuid, $timestamp)
    {
        return null;
    }

    public function sendInvoice(Invoice $invoice, $subject, $message, $recipient, $attachPdf = false)
    {
        return false;
    }

    public function getContacts($filter = '')
    {
        return [];
    }

    public function getPrimaryContact(Client $client)
    {
        return null;
    }

    public function getProductMapping(): array
    {
        return [];
    }

    public function createPayment(Payment $payment)
    {
        return null;
    }

    public function deletePayment(Payment $payment)
    {
        return true;
    }
}

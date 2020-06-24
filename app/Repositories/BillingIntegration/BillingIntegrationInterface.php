<?php
namespace App\Repositories\BillingIntegration;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;

interface BillingIntegrationInterface
{
    const INTEGRATION_TYPE = "billing";

    public function getClient();

    public function convertJson($response);

    public function createInvoice($params);

    public function bookInvoice($invoiceGuid, $timestamp);

    public function sendInvoice(Invoice $invoice, $subject, $message, $recipient, $attachPdf = false);

    public function getContacts($filter = "");

    public function getPrimaryContact(Client $client);

    public function getProductMapping(): array;

    public function createPayment(Payment $payment);

    public function deletePayment(Payment $payment);
}

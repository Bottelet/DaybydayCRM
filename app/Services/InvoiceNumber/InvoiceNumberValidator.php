<?php

namespace App\Services\InvoiceNumber;

use App\Models\Invoice;

class InvoiceNumberValidator
{
    public function validateInvoiceNumberSize(int $invoiceNumber)
    {
        return (bool) ($invoiceNumber <= 9999999 && $invoiceNumber >= 1);
    }

    public function validateInvoiceNumberIsNotLowerThenCurrentMax(int $invoiceNumber)
    {
        $currentInvoiceNumber = optional(Invoice::query()->orderByDesc('invoice_number')->limit(1)->first())->invoice_number;

        return (bool) ($invoiceNumber > $currentInvoiceNumber);
    }

    public function validateInvoiceNumber(int $invoiceNumber)
    {
        return (bool) ($this->validateInvoiceNumberIsNotLowerThenCurrentMax($invoiceNumber) && $this->validateInvoiceNumberSize($invoiceNumber));
    }
}

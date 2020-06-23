<?php

namespace App\Services\InvoiceNumber;

use App\Models\Invoice;

class InvoiceNumberValidator
{
    public function validateInvoiceNumberSize(Int $invoiceNumber)
    {
        if ($invoiceNumber <= 9999999 && $invoiceNumber >= 1) {
            return true;
        }

        return false;
    }

    public function validateInvoiceNumberIsNotLowerThenCurrentMax(Int $invoiceNumber)
    {
        $currentInvoiceNumber = optional(Invoice::query()->orderByDesc('invoice_number')->limit(1)->first())->invoice_number;
        if ($invoiceNumber > $currentInvoiceNumber) {
            return true;
        }

        return false;
    }

    public function validateInvoiceNumber(Int $invoiceNumber)
    {
        if ($this->validateInvoiceNumberIsNotLowerThenCurrentMax($invoiceNumber) && $this->validateInvoiceNumberSize($invoiceNumber)) {
            return true;
        }

        return false;
    }
}

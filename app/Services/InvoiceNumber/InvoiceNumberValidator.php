<?php

namespace App\Services\InvoiceNumber;

use App\Models\Invoice;

class InvoiceNumberValidator
{
    public function validateInvoiceNumberSize(int $invoiceNumber)
    {
        if ($invoiceNumber <= 9999999 && $invoiceNumber >= 1) {
            return true;
        }

        return false;
    }

    public function validateInvoiceNumberIsNotLowerThenCurrentMax(int $invoiceNumber)
    {
        $currentInvoiceNumber = optional(Invoice::query()->orderByDesc('invoice_number')->limit(1)->first())->invoice_number;
        if ($invoiceNumber > $currentInvoiceNumber) {
            return true;
        }

        return false;
    }

    public function validateInvoiceNumber(int $invoiceNumber)
    {
        if ($this->validateInvoiceNumberIsNotLowerThenCurrentMax($invoiceNumber) && $this->validateInvoiceNumberSize($invoiceNumber)) {
            return true;
        }

        return false;
    }
}

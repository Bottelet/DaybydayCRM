<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use App\Models\Offer;
use App\Repositories\Money\Money;
use App\Repositories\Tax\Tax;
use Exception;

class InvoiceCalculator
{
    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * @var Tax
     */
    private $tax;

    public function __construct($invoice)
    {
        if (! $invoice instanceof Invoice && ! $invoice instanceof Offer) {
            throw new Exception('Not correct type for Invoice Calculator');
        }
        $this->tax = new Tax;
        $this->invoice = $invoice;
    }

    public function getVatTotal()
    {
        $subTotal = $this->getSubTotal()->getAmount();

        return new Money((int) ($subTotal * $this->tax->vatRate()));
    }

    public function getTotalPrice(): Money
    {
        $subTotal = $this->getSubTotal()->getAmount();
        $vatTotal = $this->getVatTotal()->getAmount();

        return new Money((int) ($subTotal + $vatTotal));
    }

    public function getSubTotal(): Money
    {
        $price = 0;
        $invoiceLines = $this->invoice->invoiceLines;

        foreach ($invoiceLines as $invoiceLine) {
            $price += $invoiceLine->quantity * $invoiceLine->price;
        }

        return new Money($price);
    }

    public function getAmountDue()
    {
        return new Money((int) ($this->getTotalPrice()->getAmount() - $this->invoice->payments()->sum('amount')));
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    public function getTax()
    {
        return $this->tax;
    }
}

<?php

namespace App\Services\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\Money\Money;
use Exception;

class GenerateInvoiceStatus
{
    /**
     * @var Invoice
     */
    private $invoice;

    /** @var Money */
    private $price;

    /** @var int */
    private $sum;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->price = app(InvoiceCalculator::class, ['invoice' => $invoice])->getTotalPrice();
        $this->sum = (int) $this->invoice->payments()->sum('amount');
    }

    public function createStatus()
    {
        $this->invoice->status = $this->getStatus();

        return $this->invoice->save();
    }

    public function getStatus()
    {
        if ($this->isDraft()) {
            return InvoiceStatus::draft()->getStatus();
        }
        // If invoice amount is zero and sent, treat as paid
        if ($this->price->getAmount() == 0 && $this->invoice->isSent()) {
            return InvoiceStatus::paid()->getStatus();
        }
        if ($this->isPaid()) {
            return InvoiceStatus::paid()->getStatus();
        }
        if ($this->isOverPaid()) {
            return InvoiceStatus::overpaid()->getStatus();
        }
        if ($this->isPartialPaid()) {
            return InvoiceStatus::partialPaid()->getStatus();
        }
        if ($this->isUnPaid()) {
            return InvoiceStatus::unpaid()->getStatus();
        }
        throw new Exception("Can't generate invoice status for invoice: ".$this->invoice->id);
    }

    public function isDraft(): bool
    {
        return ! $this->invoice->isSent();
    }

    public function isPartialPaid(): bool
    {
        // Only partial if sum > 0 and less than price, and price > 0
        return $this->price->getAmount() > 0 && $this->sum > 0 && $this->sum < $this->price->getAmount();
    }

    public function isPaid(): bool
    {
        // Paid if sum equals price and price > 0, or price == 0 and sent
        return ($this->price->getAmount() > 0 && $this->price->getAmount() === $this->sum)
            || ($this->price->getAmount() == 0 && $this->invoice->isSent());
    }

    public function isUnPaid(): bool
    {
        // Unpaid if sum <= 0 and price > 0
        return $this->price->getAmount() > 0 && $this->sum <= 0;
    }

    public function isOverPaid(): bool
    {
        // Overpaid if sum > price and price > 0
        return $this->price->getAmount() > 0 && $this->sum > $this->price->getAmount();
    }
}

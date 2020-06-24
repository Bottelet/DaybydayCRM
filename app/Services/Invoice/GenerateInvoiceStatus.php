<?php
namespace App\Services\Invoice;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Repositories\Money\Money;

class GenerateInvoiceStatus
{
    /**
     * @var Invoice
     */
    private $invoice;
    /** @var Money */
    private $price;
    /** @var int  */
    private $sum;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
        $this->price = app(InvoiceCalculator::class, ['invoice' => $invoice])->getTotalPrice();
        $this->sum = (int)$this->invoice->payments()->sum('amount');
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
        if ($this->isUnPaid()) {
            return InvoiceStatus::unpaid()->getStatus();
        }
        if ($this->isPaid()) {
            return InvoiceStatus::paid()->getStatus();
        }
        if ($this->isPartialPaid()) {
            return InvoiceStatus::partialPaid()->getStatus();
        }
        if ($this->isOverPaid()) {
            return InvoiceStatus::overpaid()->getStatus();
        }
        throw new \Exception("Can't generate invoice status for invoice: " . $this->invoice->id);
    }

    public function isDraft(): bool
    {
        return !$this->invoice->isSent();
    }

    public function isPartialPaid(): bool
    {
        return $this->sum < $this->price->getAmount() && $this->sum > 0;
    }

    public function isPaid(): bool
    {
        return $this->price->getAmount() === $this->sum;
    }

    public function isUnPaid(): bool
    {
        return $this->sum <= 0 && $this->price->getAmount() !== 0;
    }

    public function isOverPaid(): bool
    {
        return $this->price->getAmount() < $this->sum;
    }
}

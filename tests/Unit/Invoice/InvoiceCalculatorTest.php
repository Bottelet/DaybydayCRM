<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\InvoiceCalculator;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceCalculatorTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $payment;

    private $invoiceLine;

    /**
     * @var Application
     */
    private $invoiceCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoice = Invoice::factory()->create([
            'sent_at' => today(),
        ]);
        $this->payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => today(),
            'payment_source' => 'test',
        ]);
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price' => 5000,
            'quantity' => 1,
            'type' => 'hours',
        ]);
        $this->invoiceCalculator = app(InvoiceCalculator::class, ['invoice' => $this->invoice]);
    }

    #[Test]
    public function get_amount_due()
    {
        $this->assertEquals(4000, $this->invoiceCalculator->getAmountDue()->getAmount());
    }
}

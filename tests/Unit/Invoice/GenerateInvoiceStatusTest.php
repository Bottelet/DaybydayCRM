<?php
namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\GenerateInvoiceStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class GenerateInvoiceStatusTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;
    private $payment;
    private $invoiceLine;
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $generateInvoiceStatus;

    public function setUp(): void
    {
        parent::setUp();
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => today()
        ]);
        $this->payment = factory(Payment::class)->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => today(),
            'payment_source' => 'test'
        ]);
        $this->invoiceLine = factory(InvoiceLine::class)->create([
            'invoice_id' => $this->invoice->id,
            'price' => 5000,
            'quantity' => 1,
            'type' => 'hours',
        ]);
        $this->generateInvoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);
    }

    /** @test */
    public function isStatusPaid()
    {
        $this->assertFalse($this->generateInvoiceStatus->isPaid());
        $this->payment->amount = 5000;
        $this->payment->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isPaid());
    }

    /** @test */
    public function isStatusPartialPaid()
    {
        $this->assertTrue($this->generateInvoiceStatus->isPartialPaid());
    }

    /** @test */
    public function isStatusOverPaid()
    {
        $this->assertFalse($this->generateInvoiceStatus->isOverPaid());
        $this->payment->amount = 6000;
        $this->payment->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isOverPaid());
    }

    /** @test */
    public function isStatusUnPaid()
    {
        $this->assertFalse($this->generateInvoiceStatus->isUnPaid());
        $this->payment->forceDelete();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isUnPaid());
    }

    /** @test */
    public function isStatusDraft()
    {
        $this->assertFalse($this->generateInvoiceStatus->isDraft());

        $this->invoice->sent_at = null;
        $this->invoice->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isDraft());
    }

    /** @test */
    public function isOnlyPartialPaidIfValuesIsBetweenInvoiceAmount()
    {
        $this->assertTrue($this->generateInvoiceStatus->isPartialPaid());

        $this->payment->amount = 5000;
        $this->payment->save();

        $this->assertFalse(app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->isPartialPaid());

        $this->payment->amount = 6000;
        $this->payment->save();

        $this->assertFalse(app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->isPartialPaid());

        $this->payment->amount = -2000;
        $this->payment->save();

        $this->assertFalse(app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->isPartialPaid());
    }

    /** @test */
    public function isNotUnpaidIfInvoiceAmountIsZero()
    {
        $this->payment->forceDelete();
        $this->invoiceLine->price = 0;
        $this->invoiceLine->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);

        $this->assertFalse($invoiceStatus->isUnPaid());
        $this->assertEquals("paid", $invoiceStatus->getStatus());
    }

    /** @test */
    public function isPaidIfInvoiceAmountIsZeroAndInvoiceIsSent()
    {
        $this->payment->forceDelete();
        $this->invoiceLine->forceDelete();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);
        $this->assertTrue($invoiceStatus->isPaid());
    }

    /** @test */
    public function isDraftIfInvoiceAmountIsZeroAndInvoiceIsNotSent()
    {
        $this->payment->forceDelete();
        $this->invoiceLine->forceDelete();
        $this->invoice->sent_at = null;
        $this->invoice->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);
        $this->assertEquals("draft", $invoiceStatus->getStatus());
    }

    /** @test */
    public function isUnpaidIfInvoiceAmountIsLessThenZero()
    {
        $this->assertFalse($this->generateInvoiceStatus->isUnPaid());
        $this->payment->amount = -3000;
        $this->payment->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);
        $this->assertTrue($invoiceStatus->isUnPaid());
    }

    /** @test */
    public function getStatusOfInvoice()
    {
        /** Clean up for complete flow */
        $this->payment->forceDelete();
        $this->invoice->sent_at = null;
        $this->invoice->save();

        $this->assertEquals("draft", app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        $this->invoice->sent_at = today();
        $this->invoice->save();

        $this->assertEquals("unpaid", app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        factory(Payment::class)->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => today(),
            'payment_source' => 'test'
        ]);
        $this->invoice->refresh();
        $this->assertEquals("partial_paid", app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        factory(Payment::class)->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 4000,
            'payment_date' => today(),
            'payment_source' => 'test'
        ]);

        $this->assertEquals("paid", app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        factory(Payment::class)->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 4000,
            'payment_date' => today(),
            'payment_source' => 'test'
        ]);

        $this->assertEquals("overpaid", app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());
    }

    /** @test */
    public function createStatusSavesToTheInvoiceModel()
    {
        $this->assertNotEquals("partial_paid", $this->invoice->status);
        app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->createStatus();
        $this->assertEquals("partial_paid", $this->invoice->refresh()->status);
    }
}

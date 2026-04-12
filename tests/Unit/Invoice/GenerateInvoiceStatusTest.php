<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\GenerateInvoiceStatus;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class GenerateInvoiceStatusTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $payment;

    private $invoiceLine;

    /**
     * @var Application
     */
    private $generateInvoiceStatus;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        // Ensure Setting exists with VAT = 0 for consistent test behavior
        // Update existing setting from seeder instead of creating a new one
        \App\Models\Setting::query()->update(['vat' => 0]);

        $this->invoice = Invoice::factory()->create([
            'sent_at' => Carbon::now(),
        ]);
        $this->payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => Carbon::now(),
            'payment_source' => 'test',
        ]);
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price' => 5000,
            'quantity' => 1,
            'type' => 'hours',
        ]);
        $this->generateInvoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    #[Group('flaky')]
    public function is_status_paid()
    {
        /** Arrange */
        $this->assertFalse($this->generateInvoiceStatus->isPaid());
        $this->payment->amount = 5000;
        $this->payment->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        /** Act */
        $result = $invoiceStatus->isPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function is_status_partial_paid()
    {
        /** Arrange */
        // Invoice with 5000 total and 1000 payment from setUp()

        /** Act */
        $result = $this->generateInvoiceStatus->isPartialPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    #[Group('flaky')]
    public function is_status_over_paid()
    {
        /** Arrange */
        $this->assertFalse($this->generateInvoiceStatus->isOverPaid());
        $this->payment->amount = 6000;
        $this->payment->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        /** Act */
        $result = $invoiceStatus->isOverPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function is_status_un_paid()
    {
        /** Arrange */
        $this->assertFalse($this->generateInvoiceStatus->isUnPaid());
        $this->payment->forceDelete();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        /** Act */
        $result = $invoiceStatus->isUnPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function is_status_draft()
    {
        /** Arrange */
        $this->assertFalse($this->generateInvoiceStatus->isDraft());
        $this->invoice->sent_at = null;
        $this->invoice->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        /** Act */
        $result = $invoiceStatus->isDraft();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    #[Group('flaky')]
    public function get_status_of_invoice()
    {
        /** Arrange */
        $this->payment->forceDelete();
        $this->invoice->sent_at = null;
        $this->invoice->save();

        /** Act & Assert - Draft status */
        $this->assertEquals('draft', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        /** Arrange - Send invoice */
        $this->invoice->sent_at = Carbon::now();
        $this->invoice->save();

        /** Act & Assert - Unpaid status */
        $this->assertEquals('unpaid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        /** Arrange - Partial payment */
        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => Carbon::now(),
            'payment_source' => 'test',
        ]);
        $this->invoice->refresh();

        /** Act & Assert - Partial paid status */
        $this->assertEquals('partial_paid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        /** Arrange - Complete payment */
        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 4000,
            'payment_date' => Carbon::now(),
            'payment_source' => 'test',
        ]);

        /** Act & Assert - Paid status */
        $this->assertEquals('paid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        /** Arrange - Overpayment */
        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 4000,
            'payment_date' => Carbon::now(),
            'payment_source' => 'test',
        ]);

        /** Act & Assert - Overpaid status */
        $this->assertEquals('overpaid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());
    }

    #[Test]
    public function create_status_saves_to_the_invoice_model()
    {
        /** Arrange */
        $this->assertNotEquals('partial_paid', $this->invoice->status);

        /** Act */
        app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->createStatus();

        /** Assert */
        $this->assertEquals('partial_paid', $this->invoice->refresh()->status);
    }

    // endregion

    // region edge_cases

    #[Test]
    #[Group('flaky')]
    public function is_only_partial_paid_if_values_is_between_invoice_amount()
    {
        /** Arrange & Assert - Initially partial paid */
        $this->assertTrue($this->generateInvoiceStatus->isPartialPaid());

        /** Arrange - Full payment */
        $this->payment->amount = 5000;
        $this->payment->save();

        /** Act & Assert - Not partial paid when fully paid */
        $this->assertFalse(app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->isPartialPaid());

        /** Arrange - Overpayment */
        $this->payment->amount = 6000;
        $this->payment->save();

        /** Act & Assert - Not partial paid when overpaid */
        $this->assertFalse(app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->isPartialPaid());

        /** Arrange - Negative payment */
        $this->payment->amount = -2000;
        $this->payment->save();

        /** Act & Assert - Not partial paid with negative payment */
        $this->assertFalse(app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->isPartialPaid());
    }

    #[Test]
    public function is_not_unpaid_if_invoice_amount_is_zero()
    {
        /** Arrange */
        $this->payment->forceDelete();
        $this->invoiceLine->price = 0;
        $this->invoiceLine->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);

        /** Act */
        $isUnpaid = $invoiceStatus->isUnPaid();
        $status = $invoiceStatus->getStatus();

        /** Assert */
        $this->assertFalse($isUnpaid);
        $this->assertEquals('paid', $status);
    }

    #[Test]
    public function is_paid_if_invoice_amount_is_zero_and_invoice_is_sent()
    {
        /** Arrange */
        $this->payment->forceDelete();
        $this->invoiceLine->forceDelete();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);

        /** Act */
        $result = $invoiceStatus->isPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function is_draft_if_invoice_amount_is_zero_and_invoice_is_not_sent()
    {
        /** Arrange */
        $this->payment->forceDelete();
        $this->invoiceLine->forceDelete();
        $this->invoice->sent_at = null;
        $this->invoice->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);

        /** Act */
        $status = $invoiceStatus->getStatus();

        /** Assert */
        $this->assertEquals('draft', $status);
    }

    #[Test]
    public function is_unpaid_if_invoice_amount_is_less_then_zero()
    {
        /** Arrange */
        $this->assertFalse($this->generateInvoiceStatus->isUnPaid());
        $this->payment->amount = -3000;
        $this->payment->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);

        /** Act */
        $result = $invoiceStatus->isUnPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function is_paid_with_multiple_payments_totaling_exact_amount()
    {
        /** Arrange */
        $this->payment->amount = 2000;
        $this->payment->save();
        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 3000,
            'payment_date' => Carbon::now(),
            'payment_source' => 'test',
        ]);
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        /** Act */
        $result = $invoiceStatus->isPaid();

        /** Assert */
        $this->assertTrue($result);
    }

    #[Test]
    public function draft_status_takes_precedence_when_not_sent()
    {
        /** Arrange */
        $this->invoice->sent_at = null;
        $this->invoice->save();
        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        /** Act */
        $status = $invoiceStatus->getStatus();

        /** Assert */
        $this->assertEquals('draft', $status);
    }

    // endregion
}

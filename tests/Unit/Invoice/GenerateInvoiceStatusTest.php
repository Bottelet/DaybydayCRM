<?php

namespace Tests\Unit\Invoice;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\GenerateInvoiceStatus;
use Illuminate\Contracts\Foundation\Application;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

        // Ensure Setting exists with VAT = 0 for consistent test behavior
        // Use firstOrCreate to ensure a setting exists, then update it
        $setting = \App\Models\Setting::firstOrCreate(
            ['id' => 1],
            [
                'client_number' => 10000,
                'invoice_number' => 10000,
                'company' => 'test company',
                'max_users' => 10,
                'currency' => 'USD',
                'language' => 'en',
                'country' => 'GB',
            ]
        );
        $setting->vat = 0;
        $setting->save();

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
        $this->generateInvoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);
    }

    #[Test]
    #[Group('flaky')]
    public function is_status_paid()
    {
        $this->assertFalse($this->generateInvoiceStatus->isPaid());
        $this->payment->amount = 5000;
        $this->payment->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isPaid());
    }

    #[Test]
    public function is_status_partial_paid()
    {
        $this->assertTrue($this->generateInvoiceStatus->isPartialPaid());
    }

    #[Test]
    #[Group('flaky')]
    public function is_status_over_paid()
    {
        $this->assertFalse($this->generateInvoiceStatus->isOverPaid());
        $this->payment->amount = 6000;
        $this->payment->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isOverPaid());
    }

    #[Test]
    public function is_status_un_paid()
    {
        $this->assertFalse($this->generateInvoiceStatus->isUnPaid());
        $this->payment->forceDelete();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isUnPaid());
    }

    #[Test]
    public function is_status_draft()
    {
        $this->assertFalse($this->generateInvoiceStatus->isDraft());

        $this->invoice->sent_at = null;
        $this->invoice->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice]);

        $this->assertTrue($invoiceStatus->isDraft());
    }

    #[Test]
    #[Group('flaky')]
    public function is_only_partial_paid_if_values_is_between_invoice_amount()
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

    #[Test]
    public function is_not_unpaid_if_invoice_amount_is_zero()
    {
        $this->payment->forceDelete();
        $this->invoiceLine->price = 0;
        $this->invoiceLine->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);

        $this->assertFalse($invoiceStatus->isUnPaid());
        $this->assertEquals('paid', $invoiceStatus->getStatus());
    }

    #[Test]
    public function is_paid_if_invoice_amount_is_zero_and_invoice_is_sent()
    {
        $this->payment->forceDelete();
        $this->invoiceLine->forceDelete();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);
        $this->assertTrue($invoiceStatus->isPaid());
    }

    #[Test]
    public function is_draft_if_invoice_amount_is_zero_and_invoice_is_not_sent()
    {
        $this->payment->forceDelete();
        $this->invoiceLine->forceDelete();
        $this->invoice->sent_at = null;
        $this->invoice->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);
        $this->assertEquals('draft', $invoiceStatus->getStatus());
    }

    #[Test]
    public function is_unpaid_if_invoice_amount_is_less_then_zero()
    {
        $this->assertFalse($this->generateInvoiceStatus->isUnPaid());
        $this->payment->amount = -3000;
        $this->payment->save();

        $invoiceStatus = app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice->refresh()]);
        $this->assertTrue($invoiceStatus->isUnPaid());
    }

    #[Test]
    #[Group('flaky')]
    public function get_status_of_invoice()
    {
        /** Clean up for complete flow */
        $this->payment->forceDelete();
        $this->invoice->sent_at = null;
        $this->invoice->save();

        $this->assertEquals('draft', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        $this->invoice->sent_at = today();
        $this->invoice->save();

        $this->assertEquals('unpaid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 1000,
            'payment_date' => today(),
            'payment_source' => 'test',
        ]);
        $this->invoice->refresh();
        $this->assertEquals('partial_paid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 4000,
            'payment_date' => today(),
            'payment_source' => 'test',
        ]);

        $this->assertEquals('paid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());

        Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount' => 4000,
            'payment_date' => today(),
            'payment_source' => 'test',
        ]);

        $this->assertEquals('overpaid', app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->getStatus());
    }

    #[Test]
    public function create_status_saves_to_the_invoice_model()
    {
        $this->assertNotEquals('partial_paid', $this->invoice->status);
        app(GenerateInvoiceStatus::class, ['invoice' => $this->invoice])->createStatus();
        $this->assertEquals('partial_paid', $this->invoice->refresh()->status);
    }
}

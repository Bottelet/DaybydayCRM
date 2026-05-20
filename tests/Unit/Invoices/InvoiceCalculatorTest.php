<?php

namespace Tests\Unit\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Invoice\InvoiceCalculator;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

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

        Carbon::setTestNow('2024-01-15 12:00:00');

        $setting = \App\Models\Setting::query()->firstOrCreate(
            ['id' => 1],
            [
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'company'        => 'test company',
                'max_users'      => 10,
                'currency'       => 'USD',
                'language'       => 'en',
                'country'        => 'GB',
            ]
        );
        $setting->vat = 0;
        $setting->save();

        $this->invoice = Invoice::factory()->create([
            'sent_at' => Carbon::now(),
        ]);
        $this->payment = Payment::factory()->create([
            'invoice_id'     => $this->invoice->id,
            'amount'         => 1000,
            'payment_date'   => Carbon::now(),
            'payment_source' => 'test',
        ]);
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price'      => 5000,
            'quantity'   => 1,
            'type'       => 'hours',
        ]);
        $this->invoiceCalculator = app(InvoiceCalculator::class, ['invoice' => $this->invoice]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    #[Test]
    #[Group('flaky')]
    public function it_calculates_amount_due_correctly()
    {
        /* Arrange */

        /* Act */
        $amountDue = $this->invoiceCalculator->getAmountDue()->getAmount();

        /* Assert */
        $this->assertEquals(4000, $amountDue);
    }

    #[Test]
    public function it_gets_amount_due_with_no_payments()
    {
        /* Arrange */
        $this->payment->forceDelete();
        $calculator = app(InvoiceCalculator::class, ['invoice' => $this->invoice]);

        /* Act */
        $amountDue = $calculator->getAmountDue()->getAmount();

        /* Assert */
        $this->assertEquals(5000, $amountDue);
    }

    #[Test]
    public function it_gets_amount_due_with_multiple_payments()
    {
        /* Arrange */
        Payment::factory()->create([
            'invoice_id'     => $this->invoice->id,
            'amount'         => 2000,
            'payment_date'   => Carbon::now(),
            'payment_source' => 'test',
        ]);
        $calculator = app(InvoiceCalculator::class, ['invoice' => $this->invoice]);

        /* Act */
        $amountDue = $calculator->getAmountDue()->getAmount();

        /* Assert */
        $this->assertEquals(2000, $amountDue);
    }

    #[Test]
    public function it_gets_amount_due_when_fully_paid()
    {
        /* Arrange */
        $this->payment->amount = 5000;
        $this->payment->save();
        $calculator = app(InvoiceCalculator::class, ['invoice' => $this->invoice]);

        /* Act */
        $amountDue = $calculator->getAmountDue()->getAmount();

        /* Assert */
        $this->assertEquals(0, $amountDue);
    }

    #[Test]
    public function it_gets_amount_due_with_zero_price_invoice()
    {
        /* Arrange */
        $this->payment->forceDelete();
        $this->invoiceLine->price = 0;
        $this->invoiceLine->save();
        $calculator = app(InvoiceCalculator::class, ['invoice' => $this->invoice->refresh()]);

        /* Act */
        $amountDue = $calculator->getAmountDue()->getAmount();

        /* Assert */
        $this->assertEquals(0, $amountDue);
    }
}

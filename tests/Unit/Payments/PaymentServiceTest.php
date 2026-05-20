<?php

namespace Tests\Unit\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payment\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

class PaymentServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private PaymentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PaymentService::class);
    }

    #[Test]
    public function it_adds_payment_to_sent_invoice()
    {
        /* Arrange */
        $invoice     = Invoice::factory()->create(['sent_at' => now()]);
        $amount      = 1000.50;
        $paymentDate = '2024-01-15';
        $source      = 'cash';

        /* Act */
        $payment = $this->service->addPayment($invoice, $amount, $paymentDate, $source);

        /* Assert */
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertNotNull($payment->external_id);
        $this->assertEquals(100050, $payment->amount); // Converted to cents
        $this->assertEquals('cash', $payment->payment_source);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount'     => 100050,
        ]);
    }

    #[Test]
    public function it_adds_payment_with_description()
    {
        /* Arrange */
        $invoice     = Invoice::factory()->create(['sent_at' => now()]);
        $description = 'Payments for invoice services';

        /* Act */
        $payment = $this->service->addPayment($invoice, 500, '2024-01-15', 'card', $description);

        /* Assert */
        $this->assertEquals($description, $payment->description);
        $this->assertEquals('bank', $payment->payment_source);
    }

    #[Test]
    public function it_converts_amount_to_cents()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => now()]);

        /* Act */
        $payment = $this->service->addPayment($invoice, 99.99, '2024-01-15', 'check');

        /* Assert */
        $this->assertEquals(9999, $payment->amount);
        $this->assertEquals('bank', $payment->payment_source);
    }

    #[Test]
    public function it_throws_exception_for_unsent_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);

        /* Act & Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add payment to unsent invoice');
        $this->service->addPayment($invoice, 100, '2024-01-15', 'cash');
    }

    #[Test]
    public function it_throws_exception_for_invalid_payment_source()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => now()]);

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->service->addPayment($invoice, 100, '2024-01-15', 'invalid_source');
    }

    #[Test]
    public function it_parses_payment_date_correctly()
    {
        /* Arrange */
        $invoice    = Invoice::factory()->create(['sent_at' => now()]);
        $dateString = '2024-01-15';

        /* Act */
        $payment = $this->service->addPayment($invoice, 100, $dateString, 'cash');

        /* Assert */
        $this->assertEquals('2024-01-15', $payment->payment_date->format('Y-m-d'));
    }

    #[Test]
    public function it_deletes_payment()
    {
        /* Arrange */
        $payment   = Payment::factory()->create();
        $paymentId = $payment->id;

        /* Act */
        $result = $this->service->deletePayment($payment);

        /* Assert */
        $this->assertTrue($result);
        $this->assertSoftDeleted('payments', ['id' => $paymentId]);
    }

    #[Test]
    public function it_finds_payment_by_external_id()
    {
        /* Arrange */
        $payment = Payment::factory()->create();

        /* Act */
        $found = $this->service->findByExternalId($payment->external_id);

        /* Assert*/
        $this->assertNotNull($found);
        $this->assertEquals($payment->id, $found->id);
    }

    #[Test]
    public function it_returns_null_for_nonexistent_payment()
    {
        /* Act */
        $found = $this->service->findByExternalId('nonexistent-id');

        /* Assert */
        $this->assertNull($found);
    }

    #[Test]
    public function it_gets_payments_for_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        Payment::factory()->count(3)->create(['invoice_id' => $invoice->id]);
        Payment::factory()->create(); // Different invoice

        /* Act */
        $payments = $this->service->getPaymentsForInvoice($invoice);

        /* Assert */
        $this->assertCount(3, $payments);
        foreach ($payments as $payment) {
            $this->assertEquals($invoice->id, $payment->invoice_id);
        }
    }

    #[Test]
    public function it_calculates_total_payments()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 10000]); // 100.00
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 50000]); // 500.00
        Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 25000]); // 250.00

        /* Act */
        $total = $this->service->calculateTotalPayments($invoice);

        /* Assert */
        $this->assertEquals(85000, $total); // 850.00 in cents
    }
}

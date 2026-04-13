<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class PaymentModelTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');

        $this->invoice = Invoice::factory()->create([
            'status' => 'unpaid',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    # region happy_path

    #[Test]
    public function it_payment_invoice_relationship_returns_belongs_to_instance()
    {
        /** Arrange */
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);

        /** Act */
        $relationship = $payment->invoice();

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relationship);
    }

    #[Test]
    public function it_payment_belongs_to_invoice()
    {
        /** Arrange */
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);

        /** Act */
        $relatedInvoice = $payment->invoice;

        /* Assert */
        $this->assertNotNull($relatedInvoice);
        $this->assertInstanceOf(Invoice::class, $relatedInvoice);
        $this->assertEquals($this->invoice->id, $relatedInvoice->id);
    }

    #[Test]
    public function it_payment_factory_creates_payment_with_invoice()
    {
        /** Arrange */
        // Payment factory automatically creates an invoice via Invoice::factory()

        /** Act */
        $payment = Payment::factory()->create();

        /* Assert */
        $this->assertNotNull($payment->invoice_id);
        $this->assertNotNull($payment->invoice);
        $this->assertInstanceOf(Invoice::class, $payment->invoice);
    }

    #[Test]
    public function it_multiple_payments_can_belong_to_same_invoice()
    {
        /** Arrange */
        $payment1 = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount'     => 500,
        ]);
        $payment2 = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
            'amount'     => 300,
        ]);

        /** Act */
        $invoice1 = $payment1->invoice;
        $invoice2 = $payment2->invoice;

        /* Assert */
        $this->assertEquals($this->invoice->id, $invoice1->id);
        $this->assertEquals($this->invoice->id, $invoice2->id);
        $this->assertEquals($invoice1->id, $invoice2->id);
    }

    #[Test]
    public function it_payment_invoice_method_exists_on_model()
    {
        /** Arrange */
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);

        /* Act & Assert */
        $this->assertTrue(method_exists($payment, 'invoice'));
    }

    # endregion

    # region edge_cases

    #[Test]
    public function it_payment_without_invoice_id_returns_null_for_invoice()
    {
        /** Arrange */
        $payment = new Payment([
            'external_id'    => 'test-uuid',
            'amount'         => 1000,
            'payment_date'   => Carbon::now(),
            'payment_source' => 'bank',
        ]);
        $payment->invoice_id = null;

        /** Act */
        $relatedInvoice = $payment->invoice;

        /* Assert */
        $this->assertNull($relatedInvoice);
    }

    #[Test]
    public function it_soft_deleted_payment_still_has_invoice_relationship()
    {
        /** Arrange */
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);
        $paymentId = $payment->id;
        $payment->delete();

        /** Act */
        $deletedPayment = Payment::withTrashed()->find($paymentId);

        /* Assert */
        $this->assertNotNull($deletedPayment);
        $this->assertNotNull($deletedPayment->invoice);
        $this->assertEquals($this->invoice->id, $deletedPayment->invoice->id);
    }

    #[Test]
    public function it_payment_invoice_relationship_returns_correct_invoice_when_multiple_invoices_exist()
    {
        /** Arrange */
        $otherInvoice = Invoice::factory()->create([
            'status' => 'draft',
        ]);
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);
        $otherPayment = Payment::factory()->create([
            'invoice_id' => $otherInvoice->id,
        ]);

        /** Act */
        $paymentInvoice      = $payment->invoice;
        $otherPaymentInvoice = $otherPayment->invoice;

        /* Assert */
        $this->assertEquals($this->invoice->id, $paymentInvoice->id);
        $this->assertEquals($otherInvoice->id, $otherPaymentInvoice->id);
        $this->assertNotEquals($paymentInvoice->id, $otherPaymentInvoice->id);
    }

    #[Test]
    public function it_payment_has_external_id_after_creation()
    {
        /** Arrange */
        // Payment factory provides external_id via faker

        /** Act */
        $payment = Payment::factory()->create([
            'invoice_id' => $this->invoice->id,
        ]);

        /* Assert */
        $this->assertNotNull($payment->external_id);
        $this->assertNotEmpty($payment->external_id);
    }

    # endregion

    # region failure_path

    #[Test]
    public function it_payment_does_not_depend_on_dingo_api()
    {
        /** Arrange */
        // No specific arrangement needed

        /** Act */
        $uses = class_uses_recursive(Payment::class);

        /* Assert */
        $this->assertNotContains('Dingo\Api\Routing\Helpers', $uses);
    }

    # endregion
}

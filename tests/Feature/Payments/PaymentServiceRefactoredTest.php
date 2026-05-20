<?php

namespace Tests\Feature\Payments;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Services\Billing\BillingIntegrationRegistry;
use App\Services\Billing\NullBillingAdapter;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\AbstractTestCase;

/**
 * Tests for the refactored PaymentService and PaymentsController.
 *
 * Verifies:
 *  - Controller delegates entirely to PaymentService.
 *  - NullBillingAdapter is used when no billing integration is configured.
 *  - Invoice status side-effects are persisted after payment operations.
 *  - Soft-delete semantics are explicit.
 *  - JSON responses are returned for JSON requests with correct status codes.
 *  - Authorization is checked before any infrastructure code runs.
 *  - Edge cases: zero amount, invalid source, comma-separated decimals.
 */
#[Group('payments')]
#[Group('integration-isolation')]
class PaymentServiceRefactoredTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Invoice $invoice;

    private InvoiceLine $invoiceLine;

    private PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2024-01-15 12:00:00');
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        \App\Models\Setting::factory()->create(['vat' => 0]);

        $this->invoice = Invoice::factory()->create([
            'sent_at' => today(),
            'status'  => 'unpaid',
        ]);
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price'      => 5000,
            'quantity'   => 1,
        ]);

        $this->paymentService = app(PaymentService::class);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // ─── PaymentService unit-ish ─────────────────────────────────────────────

    #[Test]
    public function it_uses_null_billing_adapter_when_no_integration_is_configured()
    {
        /* Arrange */
        \App\Models\Integration::whereApiType('billing')->delete();
        $registry = app(BillingIntegrationRegistry::class);
        $registry->reset();

        /* Act */
        $driver = $registry->driver();

        /* Assert */
        $this->assertInstanceOf(NullBillingAdapter::class, $driver);
    }

    #[Test]
    public function it_creates_a_payment_record()
    {
        /* Arrange */
        $this->assertDatabaseCount('payments', 0);

        /* Act */
        $payment = $this->paymentService->addPayment(
            $this->invoice,
            50.00,
            '2024-01-15',
            'bank',
            'Test payment'
        );

        /* Assert – database state */
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertDatabaseHas('payments', [
            'invoice_id'     => $this->invoice->id,
            'amount'         => 5000, // in cents
            'payment_source' => 'bank',
        ]);
    }

    #[Test]
    public function it_marks_invoice_as_paid_after_full_payment()
    {
        /* Arrange – invoice has one line of price=5000, qty=1 → total 5000 cents */
        $this->assertDatabaseHas('invoices', ['id' => $this->invoice->id, 'status' => 'unpaid']);

        /* Act – pay the exact total (50.00 = 5000 cents) */
        $this->paymentService->addPayment(
            $this->invoice,
            50.00,
            '2024-01-15',
            'bank'
        );

        /* Assert – invoice status side-effect persisted to DB */
        $this->assertDatabaseHas('invoices', ['id' => $this->invoice->id, 'status' => 'paid']);
    }

    #[Test]
    public function it_marks_invoice_as_partial_after_a_partial_payment()
    {
        /* Arrange */
        $this->assertDatabaseHas('invoices', ['id' => $this->invoice->id, 'status' => 'unpaid']);

        /* Act – pay less than the total */
        $this->paymentService->addPayment(
            $this->invoice,
            10.00, // only 1000 cents out of 5000
            '2024-01-15',
            'cash'
        );

        /* Assert – status becomes partial */
        $this->assertDatabaseHas('invoices', ['id' => $this->invoice->id, 'status' => 'partial_paid']);
    }

    #[Test]
    public function it_throws_when_adding_payment_to_an_unsent_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);

        /* Act & Assert */
        $this->expectException(RuntimeException::class);
        $this->paymentService->addPayment($invoice, 50.00, '2024-01-15', 'bank');
    }

    #[Test]
    public function it_throws_when_the_payment_source_is_invalid()
    {
        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->paymentService->addPayment(
            $this->invoice,
            50.00,
            '2024-01-15',
            'not_a_real_source'
        );
    }

    #[Test]
    public function it_soft_deletes_the_payment_record()
    {
        /* Arrange */
        $payment = Payment::factory()->create([
            'invoice_id'     => $this->invoice->id,
            'amount'         => 1000,
            'payment_source' => 'bank',
        ]);

        /* Act */
        $result = $this->paymentService->deletePayment($payment);

        /* Assert – soft delete, not hard delete (assertSoftDeleted confirms record exists with non-null deleted_at) */
        $this->assertTrue($result);
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    #[Test]
    public function it_deletes_payment_when_no_billing_adapter_is_configured()
    {
        /* Arrange */
        \App\Models\Integration::whereApiType('billing')->delete();
        app(BillingIntegrationRegistry::class)->reset();

        $payment = Payment::factory()->create([
            'invoice_id'     => $this->invoice->id,
            'amount'         => 1000,
            'payment_source' => 'cash',
        ]);

        /* Act – should not throw */
        $result = $this->paymentService->deletePayment($payment);

        /* Assert */
        $this->assertTrue($result);
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    // ─── Controller HTTP layer ───────────────────────────────────────────────

    #[Test]
    public function it_returns_201_json_when_payment_is_added()
    {
        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 50,
            'payment_date' => '2024-01-15',
            'source'       => 'bank',
        ]);

        /* Assert */
        $response->assertStatus(201);
        $response->assertJsonFragment(['message' => __('Payment successfully added')]);
        $this->assertDatabaseCount('payments', 1);
    }

    #[Test]
    public function it_returns_422_when_payment_is_added_to_unsent_invoice()
    {
        /* Arrange */
        $unsent = Invoice::factory()->create(['sent_at' => null]);

        /* Act */
        $response = $this->json('POST', route('payment.add', $unsent->external_id), [
            'amount'       => 50,
            'payment_date' => '2024-01-15',
            'source'       => 'bank',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    public function it_returns_422_when_payment_amount_is_zero()
    {
        /* Act – PaymentRequest has not_in:0 rule */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 0,
            'payment_date' => '2024-01-15',
            'source'       => 'bank',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    public function it_returns_422_when_payment_date_is_missing()
    {
        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 50,
            'source' => 'bank',
            // payment_date intentionally missing
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_date']);
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    public function it_returns_422_when_payment_source_is_invalid()
    {
        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 50,
            'payment_date' => '2024-01-15',
            'source'       => 'invalid_source',
        ]);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source']);
        $this->assertDatabaseCount('payments', 0);
    }

    #[Test]
    public function it_accepts_comma_decimal_notation_for_payment_amount()
    {
        /* Act – prepareForValidation normalises "50,00" to "50.00" */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => '50,00',
            'payment_date' => '2024-01-15',
            'source'       => 'bank',
        ]);

        /* Assert */
        $response->assertStatus(201);
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $this->invoice->id,
            'amount'     => 5000, // stored in cents
        ]);
    }

    #[Test]
    public function it_returns_200_json_when_payment_is_deleted()
    {
        /* Arrange */
        $payment = Payment::factory()->create([
            'invoice_id'     => $this->invoice->id,
            'amount'         => 1000,
            'payment_source' => 'bank',
        ]);

        /* Act */
        $response = $this->json('DELETE', route('payment.destroy', $payment->external_id));

        /* Assert */
        $response->assertStatus(200);
        $response->assertJsonFragment(['message' => __('Payment successfully deleted')]);
        $this->assertSoftDeleted('payments', ['id' => $payment->id]);
    }

    #[Test]
    public function it_returns_403_when_deleting_payment_without_permission()
    {
        /* Arrange */
        $payment = Payment::factory()->create([
            'invoice_id'     => $this->invoice->id,
            'amount'         => 1000,
            'payment_source' => 'bank',
        ]);
        $noPerms = \App\Models\User::factory()->create();
        $this->actingAs($noPerms);

        /* Act */
        $response = $this->json('DELETE', route('payment.destroy', $payment->external_id));

        /* Assert – 403 before any infrastructure code runs; record still exists */
        $response->assertStatus(403);
        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'deleted_at' => null]);
    }

    #[Test]
    public function it_returns_403_when_adding_payment_without_permission()
    {
        /* Arrange */
        $noPerms = \App\Models\User::factory()->create();
        $this->actingAs($noPerms);

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 50,
            'payment_date' => '2024-01-15',
            'source'       => 'bank',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertDatabaseCount('payments', 0);
    }
}

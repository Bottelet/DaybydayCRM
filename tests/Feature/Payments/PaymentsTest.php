<?php

namespace Tests\Feature\Payments;

use App\Http\Controllers\PaymentsController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(PaymentsController::class)]
class PaymentsTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    private $payment;

    protected function setUp(): void
    {
        parent::setUp();
        // Controllers check expectsJson() to decide response format; set Accept globally
        $this->defaultHeaders['Accept'] = 'application/json';
        $this->payment                  = Payment::factory()->create();

        $this->asOwner();
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        \App\Models\Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'client_number'  => 10000,
                'invoice_number' => 10000,
                'country'        => 'US',
                'company'        => 'Test Company',
                'max_users'      => 10,
                'vat'            => 0,
                'currency'       => 'USD',
                'language'       => 'en',
            ]
        );
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = Invoice::factory()->create([
            'sent_at' => today(),
            'status'  => 'unpaid',
        ]);
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price'      => 5000,
            'quantity'   => 1,
            'type'       => 'hours',
        ]);
    }

    #[Test]
    public function it_can_add_payment(): void
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 50,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertTrue($isEmpty);
        $response->assertStatus(201);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    }

    #[Test]
    public function it_can_add_payment_with_decimals_dot_separator(): void
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 50.234,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertTrue($isEmpty);
        $response->assertStatus(201);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    }

    #[Test]
    public function it_can_add_payment_with_decimals_comma_separator(): void
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => '50,234',
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertTrue($isEmpty);
        $response->assertStatus(201);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    }

    #[Test]
    public function it_can_add_payment_with_minus_amount(): void
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => -50,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertTrue($isEmpty);
        $response->assertStatus(201);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
        $this->assertEquals(-5000, $this->invoice->refresh()->payments->first()->amount);
    }

    #[Test]
    public function it_can_add_negative_payment_with_comma_separator(): void
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => '-5000,234',
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertTrue($isEmpty);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
        $response->assertStatus(201);
    }

    #[Test]
    public function it_can_add_negative_payment_with_dot_separator(): void
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => -5000.234,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertTrue($isEmpty);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
        $response->assertStatus(201);
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_cannot_create_payment_if_no_permission(): void
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 5000,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'AThisVeryColInvoice12313',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertTrue(Payment::query()->where('description', 'AThisVeryColInvoice12313')->get()->isEmpty());
    }

    #[Test]
    public function it_cannot_add_payment_where_amount_is_0(): void
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 0,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_can_delete_payment(): void
    {
        /* Arrange */
        $paymentId = $this->payment->id;

        /* Act */
        $this->delete(route('payment.destroy', $this->payment->external_id));

        /* Assert */
        $this->assertNull(Payment::find($paymentId));
        $this->assertNotNull(Payment::withTrashed()->find($paymentId));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_cannot_delete_payment_if_no_permission(): void
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());
        $payment = Payment::factory()->create();

        /* Act */
        $response = $this->delete(route('payment.destroy', $payment->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertNotNull(Payment::find($payment->id));
    }

    #[Test]
    public function it_updates_invoice_status_when_payment_is_added(): void
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 50,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(201);
        $this->assertEquals('paid', $this->invoice->refresh()->status);
    }

    #[Test]
    public function it_rejects_payment_with_wrong_amount(): void
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 'a string',
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['amount']);
    }

    #[Test]
    public function it_rejects_payment_with_wrong_source(): void
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 5000,
            'payment_date' => '2020-01-01',
            'source'       => 'invalid_source',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['source']);
    }

    #[Test]
    public function it_rejects_payment_with_invalid_date(): void
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->post(route('payment.add', $this->invoice->external_id), [
            'amount'       => 5000,
            'payment_date' => '2020-15-15',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_date']);
    }
}

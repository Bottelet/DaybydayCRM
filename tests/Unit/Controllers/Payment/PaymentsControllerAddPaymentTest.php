<?php

namespace Tests\Unit\Controllers\Payment;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentsControllerAddPaymentTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asOwner();

        // Clear permission cache to ensure fresh permission check
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();

        // Ensure Setting exists with VAT = 0 for consistent test behavior
        \App\Models\Setting::query()->update(['vat' => 0]);

        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = Invoice::factory()->create([
            'sent_at' => today(),
            'status' => 'unpaid',
        ]);
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price' => 5000,
            'quantity' => 1,
            'type' => 'hours',
        ]);
    }

    #[Test]
    public function can_add_payment()
    {
        $this->assertTrue($this->invoice->payments->isEmpty());
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 50,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(302);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    }

    #[Test]
    public function can_add_payment_with_decimals_dot_separator()
    {
        $this->assertTrue($this->invoice->payments->isEmpty());
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 50.234,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(302);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    }

    #[Test]
    public function can_add_payment_with_decimals_comma_separator()
    {
        $this->assertTrue($this->invoice->payments->isEmpty());
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => '50,234',
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(302);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
    }

    #[Test]
    public function adding_payment_updates_invoice_status()
    {
        $this->assertEquals('unpaid', $this->invoice->status);
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 50,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(302);
        $this->assertEquals('paid', $this->invoice->refresh()->status);
    }

    #[Test]
    public function adding_wrong_amount_parameter_return_error()
    {
        $this->assertEquals('unpaid', $this->invoice->status);
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 'a string',
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function adding_wrong_source_parameter_return_error()
    {
        $this->assertEquals('unpaid', $this->invoice->status);
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 5000,
            'payment_date' => '2020-01-01',
            'source' => 'invalid_source',
            'description' => 'A random description',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function adding_invalid_payment_date_parameter_return_error()
    {
        $this->assertEquals('unpaid', $this->invoice->status);
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 5000,
            'payment_date' => '2020-15-15',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(422);
    }

    #[Test]
    public function can_add_payment_with_minus_amount()
    {
        $this->assertTrue($this->invoice->payments->isEmpty());
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => -50,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(302);
        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
        $this->assertEquals(-5000, $this->invoice->refresh()->payments->first()->amount);
    }

    #[Test]
    public function can_add_negative_payment_with_comma_separator()
    {
        $this->assertTrue($this->invoice->payments->isEmpty());
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => -5000, 234,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
        $response->assertStatus(302);
    }

    #[Test]
    public function can_add_negative_payment_with_dot_separator()
    {
        $this->assertTrue($this->invoice->payments->isEmpty());
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => -5000.234,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $this->assertFalse($this->invoice->refresh()->payments->isEmpty());
        $response->assertStatus(302);
    }

    #[Test]
    public function cant_add_payment_where_amount_is_0()
    {
        $this->assertEquals('unpaid', $this->invoice->status);
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 0,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'A random description',
        ]);

        $response->assertStatus(422);
    }
}

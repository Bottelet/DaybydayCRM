<?php

namespace Tests\Feature\Payments;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class PaymentsControllerAddPaymentTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->asOwner();
        \Illuminate\Support\Facades\Cache::tags('role_user')->flush();
        \App\Models\Setting::updateOrCreate(
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
    public function it_can_add_payment()
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_can_add_payment_with_decimals_dot_separator()
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_can_add_payment_with_decimals_comma_separator()
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_adding_payment_updates_invoice_status()
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_adding_wrong_amount_parameter_return_error()
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 'a string',
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
    }

    #[Test]
    public function it_adding_wrong_source_parameter_return_error()
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 5000,
            'payment_date' => '2020-01-01',
            'source'       => 'invalid_source',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
    }

    #[Test]
    public function it_adding_invalid_payment_date_parameter_return_error()
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 5000,
            'payment_date' => '2020-15-15',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
    }

    #[Test]
    public function it_can_add_payment_with_minus_amount()
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_can_add_negative_payment_with_comma_separator()
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_can_add_negative_payment_with_dot_separator()
    {
        /* Arrange */
        $isEmpty = $this->invoice->payments->isEmpty();

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
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
    public function it_cant_add_payment_where_amount_is_0()
    {
        /* Arrange */
        $invoiceStatus = $this->invoice->status;

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 0,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'A random description',
        ]);

        /* Assert */
        $this->assertEquals('unpaid', $invoiceStatus);
        $response->assertStatus(422);
    }
}

<?php

namespace Tests\Unit\Controllers\Payment;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Models\Role;
use App\Models\User;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PaymentsControllerTest extends TestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    private $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user->attachRole(Role::whereName('owner')->first());
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = Invoice::factory()->create([
            'sent_at' => today(),
            'status' => 'unpaid',
        ]);

        $this->payment = Payment::factory()->create();
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price' => 5000,
            'quantity' => 1,
            'type' => 'hours',
        ]);
    }

    #[Test]
    public function can_delete_payment()
    {
        $this->json('delete', route('payment.destroy', $this->payment->external_id));

        $this->assertNull(Payment::find($this->payment->id));
        $this->assertNotNull(Payment::withTrashed()->find($this->payment->id));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function cant_delete_payment_if_no_permission()
    {
        $this->actingAs(User::factory()->create());
        $payment = Payment::factory()->create();

        $response = $this->json('delete', route('payment.destroy', $payment->external_id));

        $response->assertStatus(302);

        $this->assertNotNull(Payment::find($payment->id));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function cant_create_payment_if_no_permission()
    {
        $this->actingAs(User::factory()->create());

        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 5000,
            'payment_date' => '2020-01-01',
            'source' => 'bank',
            'description' => 'AThisVeryColInvoice12313',
        ]);

        $response->assertStatus(403);
        $this->assertTrue(Payment::where('description', 'AThisVeryColInvoice12313')->get()->isEmpty());
    }
}

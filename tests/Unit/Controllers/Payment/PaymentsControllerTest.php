<?php
namespace Tests\Unit\Controllers\Payment;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\PermissionRole;
use App\Models\Project;
use App\Models\RoleUser;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PaymentsControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $invoice;
    private $invoiceLine;
    private $payment;

    public function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = factory(Invoice::class)->create([
            'sent_at' => today(),
            'status' => 'unpaid'
        ]);

        $this->payment = factory(Payment::class)->create();
        $this->invoiceLine = factory(InvoiceLine::class)->create([
            'invoice_id' => $this->invoice->id,
            'price' => 5000,
            'quantity' => 1,
            'type' => 'hours',
        ]);
    }

    /** @test */
    public function can_delete_payment()
    {
        $this->json('delete', route('payment.destroy', $this->payment->external_id));

        $this->assertNull(Payment::find($this->payment->id));
        $this->assertNotNull(Payment::withTrashed()->find($this->payment->id));
    }

    /** @test */
    public function cant_delete_payment_if_no_permission()
    {
        $this->actingAs(factory(User::class)->create());
        $payment = factory(Payment::class)->create();

        $response = $this->json('delete', route('payment.destroy', $payment->external_id));

        $response->assertStatus(302);

        $this->assertNotNull(Payment::find($payment->id));
    }

    /** @test */
    public function cant_create_payment_if_no_permission()
    {
        $this->actingAs(factory(User::class)->create());

        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount' => 5000,
            'payment_date' => "2020-01-01",
            'source' => "bank",
            'description' => "AThisVeryColInvoice12313",
        ]);

        $response->assertStatus(403);
        $this->assertTrue(Payment::where('description', 'AThisVeryColInvoice12313')->get()->isEmpty());
    }
}

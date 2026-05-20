<?php

namespace Tests\Feature\Payments;

use App\Enums\PermissionName;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class PaymentsControllerTest extends AbstractTestCase
{
    use RefreshDatabase;

    private $invoice;

    private $invoiceLine;

    private $payment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withPermissions(PermissionName::PAYMENT_DELETE);
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = Invoice::factory()->create([
            'sent_at' => today(),
            'status'  => 'unpaid',
        ]);
        $this->payment     = Payment::factory()->create();
        $this->invoiceLine = InvoiceLine::factory()->create([
            'invoice_id' => $this->invoice->id,
            'price'      => 5000,
            'quantity'   => 1,
            'type'       => 'hours',
        ]);
    }

    #[Test]
    public function it_can_delete_payment()
    {
        /* Arrange */
        $paymentId = $this->payment->id;

        /* Act */
        $this->json('delete', route('payment.destroy', $this->payment->external_id));

        /* Assert */
        $this->assertNull(Payment::find($paymentId));
        $this->assertNotNull(Payment::withTrashed()->find($paymentId));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_cannot_delete_payment_if_no_permission()
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());
        $payment = Payment::factory()->create();

        /* Act */
        $response = $this->json('delete', route('payment.destroy', $payment->external_id));

        /* Assert */
        $response->assertStatus(403);
        $this->assertNotNull(Payment::find($payment->id));
    }

    #[Test]
    #[Group('junie_repaired')]
    public function it_cannot_create_payment_if_no_permission()
    {
        /* Arrange */
        $this->actingAs(User::factory()->create());

        /* Act */
        $response = $this->json('POST', route('payment.add', $this->invoice->external_id), [
            'amount'       => 5000,
            'payment_date' => '2020-01-01',
            'source'       => 'bank',
            'description'  => 'AThisVeryColInvoice12313',
        ]);

        /* Assert */
        $response->assertStatus(403);
        $this->assertTrue(Payment::query()->where('description', 'AThisVeryColInvoice12313')->get()->isEmpty());
    }
}

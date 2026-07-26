<?php

namespace Tests\Feature\Invoices;

use App\Http\Controllers\InvoicesController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

#[CoversClass(InvoicesController::class)]
class AddInvoiceLineTest extends AbstractTestCase
{
    use RefreshDatabase;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([VerifyCsrfToken::class]);
        $this->invoice = Invoice::factory()->create();
    }

    #[Test]
    public function it_creates_invoice_line_with_valid_payload(): void
    {
        /* Arrange */
        $payload = [
            'title'    => 'Consulting Hours',
            'quantity' => 3,
            'type'     => 'hours',
            'price'    => 49.99,
        ];

        /* Act */
        $response = $this->post(route('invoice.new.item', $this->invoice->external_id), $payload);

        /* Assert */
        $response->assertStatus(302);
        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $this->invoice->id,
            'title'      => 'Consulting Hours',
            'type'       => 'hours',
            'quantity'   => 3,
            'price'      => 4999,
        ]);
    }

    #[Test]
    public function it_rejects_invoice_line_creation_when_title_is_missing(): void
    {
        /* Arrange */
        $payload = [
            'quantity' => 3,
            'type'     => 'hours',
            'price'    => 49.99,
        ];

        /* Act */
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('invoice.new.item', $this->invoice->external_id), $payload);

        /* Assert */
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $this->assertDatabaseMissing('invoice_lines', [
            'invoice_id' => $this->invoice->id,
        ]);
    }
}

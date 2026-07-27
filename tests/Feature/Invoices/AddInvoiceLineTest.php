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

    #[Test]
    public function it_returns_not_found_when_adding_a_line_to_an_unknown_invoice(): void
    {
        /* Arrange */
        $payload = [
            'title'    => 'Consulting Hours',
            'quantity' => 3,
            'type'     => 'hours',
            'price'    => 49.99,
        ];

        /* Act */
        $response = $this->withHeaders(['Accept' => 'application/json'])
            ->post(route('invoice.new.item', 'not-a-real-external-id'), $payload);

        /* Assert */
        $response->assertStatus(404);
    }

    #[Test]
    public function it_creates_multiple_invoice_lines_via_batch_endpoint(): void
    {
        /* Arrange */
        $payload = [
            ['title' => 'Line One', 'quantity' => 1, 'type' => 'hours', 'price' => 10],
            ['title' => 'Line Two', 'quantity' => 2, 'type' => 'hours', 'price' => 20],
        ];

        /* Act */
        $response = $this->postJson(route('create.invoiceLine', $this->invoice->external_id), $payload);

        /* Assert */
        $response->assertStatus(200);
        $this->assertDatabaseHas('invoice_lines', ['invoice_id' => $this->invoice->id, 'title' => 'Line One']);
        $this->assertDatabaseHas('invoice_lines', ['invoice_id' => $this->invoice->id, 'title' => 'Line Two']);
    }

    #[Test]
    public function it_rejects_batch_invoice_lines_when_an_entry_is_not_an_array(): void
    {
        /* Arrange */
        $payload = [
            ['title' => 'Line One', 'quantity' => 1, 'type' => 'hours', 'price' => 10],
            'not-an-array',
        ];

        /* Act */
        $response = $this->postJson(route('create.invoiceLine', $this->invoice->external_id), $payload);

        /* Assert */
        $response->assertStatus(422);
        $this->assertDatabaseMissing('invoice_lines', ['invoice_id' => $this->invoice->id]);
    }

    #[Test]
    public function it_rolls_back_all_batch_invoice_lines_when_one_entry_fails_validation(): void
    {
        /* Arrange: second entry is missing the required title field. */
        $payload = [
            ['title' => 'Line One', 'quantity' => 1, 'type' => 'hours', 'price' => 10],
            ['quantity' => 2, 'type' => 'hours', 'price' => 20],
        ];

        /* Act */
        $response = $this->postJson(route('create.invoiceLine', $this->invoice->external_id), $payload);

        /* Assert: nothing persists, including the earlier valid line. */
        $response->assertStatus(422);
        $this->assertDatabaseMissing('invoice_lines', ['invoice_id' => $this->invoice->id, 'title' => 'Line One']);
    }

    #[Test]
    public function it_returns_not_found_when_batch_adding_lines_to_an_unknown_invoice(): void
    {
        /* Arrange */
        $payload = [
            ['title' => 'Line One', 'quantity' => 1, 'type' => 'hours', 'price' => 10],
        ];

        /* Act */
        $response = $this->postJson(route('create.invoiceLine', 'not-a-real-external-id'), $payload);

        /* Assert */
        $response->assertStatus(404);
    }
}

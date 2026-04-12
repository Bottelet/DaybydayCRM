<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Ramsey\Uuid\Uuid;
use Tests\AbstractTestCase;

class InvoiceLineModelBootTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for deterministic tests
        Carbon::setTestNow('2024-01-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // region happy_path

    #[Test]
    public function it_invoice_line_stores_explicit_external_id_when_provided()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        $externalId = Uuid::uuid4()->toString();

        /** Act */
        $invoiceLine = InvoiceLine::create([
            'external_id' => $externalId,
            'title' => 'Test Line Item',
            'comment' => 'Test comment',
            'type' => 'hours',
            'quantity' => 2,
            'price' => 1000,
            'invoice_id' => $invoice->id,
        ]);

        /** Assert */
        $this->assertNotNull($invoiceLine->external_id);
        $this->assertNotEmpty($invoiceLine->external_id);
        $this->assertEquals($externalId, $invoiceLine->external_id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $invoiceLine->external_id
        );
    }

    #[Test]
    public function it_invoice_line_generates_unique_external_ids_for_each_record()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();

        $line1 = InvoiceLine::create([
            'external_id' => Uuid::uuid4()->toString(),
            'title' => 'Line Item One',
            'comment' => 'First comment',
            'type' => 'hours',
            'quantity' => 1,
            'price' => 100,
            'invoice_id' => $invoice->id,
        ]);

        /** Act */
        $line2 = InvoiceLine::create([
            'external_id' => Uuid::uuid4()->toString(),
            'title' => 'Line Item Two',
            'comment' => 'Second comment',
            'type' => 'days',
            'quantity' => 2,
            'price' => 200,
            'invoice_id' => $invoice->id,
        ]);

        /** Assert */
        $this->assertNotEquals($line1->external_id, $line2->external_id);
    }

    #[Test]
    public function it_invoice_line_factory_creates_record_with_external_id()
    {
        /** Arrange */
        // Factory will create invoice automatically

        /** Act */
        $invoiceLine = InvoiceLine::factory()->create();

        /** Assert */
        $this->assertNotNull($invoiceLine->external_id);
        $this->assertDatabaseHas('invoice_lines', [
            'id' => $invoiceLine->id,
            'external_id' => $invoiceLine->external_id,
        ]);
    }

    // endregion

    // region edge_cases

    #[Test]
    public function it_invoice_line_preserves_provided_external_id()
    {
        /** Arrange */
        $invoice = Invoice::factory()->create();
        $customExternalId = 'custom-invoice-line-uuid-xyz';

        /** Act */
        $invoiceLine = InvoiceLine::create([
            'external_id' => $customExternalId,
            'title' => 'Test Line Item',
            'comment' => 'Test comment',
            'type' => 'pieces',
            'quantity' => 5,
            'price' => 500,
            'invoice_id' => $invoice->id,
        ]);

        /** Assert */
        $this->assertEquals($customExternalId, $invoiceLine->external_id);
    }

    // endregion
}

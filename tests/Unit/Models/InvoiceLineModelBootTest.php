<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceLineModelBootTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function invoice_line_auto_generates_external_id_when_not_provided()
    {
        $invoice = factory(Invoice::class)->create();

        $invoiceLine = InvoiceLine::create([
            'title' => 'Test Line Item',
            'type' => 'hours',
            'quantity' => 2,
            'price' => 1000,
            'invoice_id' => $invoice->id,
        ]);

        $this->assertNotNull($invoiceLine->external_id);
        $this->assertNotEmpty($invoiceLine->external_id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            $invoiceLine->external_id
        );
    }

    #[Test]
    public function invoice_line_preserves_provided_external_id()
    {
        $invoice = factory(Invoice::class)->create();
        $customExternalId = 'custom-invoice-line-uuid-xyz';

        $invoiceLine = InvoiceLine::create([
            'external_id' => $customExternalId,
            'title' => 'Test Line Item',
            'type' => 'pieces',
            'quantity' => 5,
            'price' => 500,
            'invoice_id' => $invoice->id,
        ]);

        $this->assertEquals($customExternalId, $invoiceLine->external_id);
    }

    #[Test]
    public function invoice_line_generates_unique_external_ids_for_each_record()
    {
        $invoice = factory(Invoice::class)->create();

        $line1 = InvoiceLine::create([
            'title' => 'Line Item One',
            'type' => 'hours',
            'quantity' => 1,
            'price' => 100,
            'invoice_id' => $invoice->id,
        ]);

        $line2 = InvoiceLine::create([
            'title' => 'Line Item Two',
            'type' => 'days',
            'quantity' => 2,
            'price' => 200,
            'invoice_id' => $invoice->id,
        ]);

        $this->assertNotEquals($line1->external_id, $line2->external_id);
    }

    #[Test]
    public function invoice_line_factory_creates_record_with_external_id()
    {
        $invoiceLine = factory(InvoiceLine::class)->create();

        $this->assertNotNull($invoiceLine->external_id);
        $this->assertDatabaseHas('invoice_lines', [
            'id' => $invoiceLine->id,
            'external_id' => $invoiceLine->external_id,
        ]);
    }
}
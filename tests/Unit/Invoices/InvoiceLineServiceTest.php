<?php

namespace Tests\Unit\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Product;
use App\Services\InvoiceLine\InvoiceLineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class InvoiceLineServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private InvoiceLineService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InvoiceLineService();
    }

    #[Test]
    public function it_creates_invoice_line()
    {
        /* Arrange */
        $invoice  = Invoice::factory()->create(['sent_at' => null]);
        $title    = 'Professional Services';
        $type     = 'service';
        $quantity = 2;
        $price    = 500.00;

        /* Act */
        $line = $this->service->createLine($invoice, $title, $type, $quantity, $price);

        /* Assert */
        $this->assertInstanceOf(InvoiceLine::class, $line);
        $this->assertNotNull($line->external_id);
        $this->assertEquals($title, $line->title);
        $this->assertEquals($type, $line->type);
        $this->assertEquals($quantity, $line->quantity);
        $this->assertEquals(50000, $line->price); // 500.00 in cents
        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $invoice->id,
            'title'      => $title,
        ]);
    }

    #[Test]
    public function it_creates_line_with_comment_and_product()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);
        $product = Product::factory()->create();
        $comment = 'Custom work';

        /* Act */
        $line = $this->service->createLine($invoice, 'Title', 'service', 1, 100, $comment, $product->id);

        /* Assert */
        $this->assertEquals($comment, $line->comment);
        $this->assertEquals($product->id, $line->product_id);
    }

    #[Test]
    public function it_throws_exception_for_sent_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => now()]);

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add lines to a sent invoice');
        $this->service->createLine($invoice, 'Title', 'service', 1, 100);
    }

    #[Test]
    public function it_creates_line_from_product_external_id()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);
        $product = Product::factory()->create();

        /* Act */
        $line = $this->service->createLineFromProduct(
            $invoice,
            'Product Line',
            'product',
            3,
            75.50,
            'Qty 3 @ $75.50 each',
            $product->external_id
        );

        /* Assert */
        $this->assertEquals($product->id, $line->product_id);
        $this->assertEquals(7550, $line->price);
        $this->assertDatabaseHas('invoice_lines', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
        ]);
    }

    #[Test]
    public function it_handles_nonexistent_product_external_id()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);

        /* Act */
        $line = $this->service->createLineFromProduct(
            $invoice,
            'Line',
            'service',
            1,
            100,
            null,
            'nonexistent-product-id'
        );

        /* Assert */
        $this->assertNull($line->product_id);
    }

    #[Test]
    public function it_deletes_line_from_draft_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);
        $line    = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);
        $lineId  = $line->id;

        /* Act */
        $result = $this->service->deleteLine($line);

        /* Assert */
        $this->assertTrue($result);
        $this->assertSoftDeleted('invoice_lines', ['id' => $lineId]);
    }

    #[Test]
    public function it_throws_exception_when_deleting_from_sent_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => now()]);
        $line    = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot delete lines from a sent invoice');
        $this->service->deleteLine($line);
    }

    #[Test]
    public function it_updates_line_in_draft_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => null]);
        $line    = InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'quantity' => 1]);

        $updateData = ['quantity' => 5, 'price' => 150.00];

        /* Act */
        $result = $this->service->updateLine($line, $updateData);

        /* Assert */
        $this->assertTrue($result);
        $fresh = $line->fresh();
        $this->assertEquals(5, $fresh->quantity);
        $this->assertEquals(15000, $fresh->price);
    }

    #[Test]
    public function it_throws_exception_when_updating_sent_invoice_line()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create(['sent_at' => now()]);
        $line    = InvoiceLine::factory()->create(['invoice_id' => $invoice->id]);

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot update lines on a sent invoice');
        $this->service->updateLine($line, ['quantity' => 10]);
    }

    #[Test]
    public function it_finds_line_by_external_id()
    {
        /* Arrange */
        $line = InvoiceLine::factory()->create();

        /* Act */
        $found = $this->service->findByExternalId($line->external_id);

        /* Assert */
        $this->assertNotNull($found);
        $this->assertEquals($line->id, $found->id);
    }

    #[Test]
    public function it_returns_null_for_nonexistent_line()
    {
        /* Act */
        $found = $this->service->findByExternalId('nonexistent-id');

        /* Assert */
        $this->assertNull($found);
    }

    #[Test]
    public function it_gets_all_lines_for_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        InvoiceLine::factory()->count(3)->create(['invoice_id' => $invoice->id]);
        InvoiceLine::factory()->create(); // Different invoice

        /* Act */
        $lines = $this->service->getLinesForInvoice($invoice);

        /* Assert */
        $this->assertCount(3, $lines);
        foreach ($lines as $line) {
            $this->assertEquals($invoice->id, $line->invoice_id);
        }
    }

    #[Test]
    public function it_calculates_total_for_invoice()
    {
        /* Arrange */
        $invoice = Invoice::factory()->create();
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'price' => 10000, 'quantity' => 2]); // 200.00
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'price' => 50000, 'quantity' => 1]); // 500.00
        InvoiceLine::factory()->create(['invoice_id' => $invoice->id, 'price' => 25000, 'quantity' => 3]); // 750.00

        /* Act */
        $total = $this->service->calculateTotal($invoice);

        /* Assert */
        $this->assertEquals(145000, $total); // 1450.00 in cents
    }
}

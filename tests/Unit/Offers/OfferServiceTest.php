<?php

namespace Tests\Unit\Offers;

use App\Enums\OfferStatus;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Services\Offer\OfferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\AbstractTestCase;

class OfferServiceTest extends AbstractTestCase
{
    use RefreshDatabase;

    private OfferService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OfferService();
    }

    #[Test]
    public function it_creates_offer_with_lines()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $lines = [
            [
                'title'    => 'Service 1',
                'type'     => 'service',
                'price'    => 100.00,
                'quantity' => 1,
                'comment'  => 'First service',
            ],
            [
                'title'    => 'Service 2',
                'type'     => 'service',
                'price'    => 50.00,
                'quantity' => 2,
            ],
        ];

        /* Act */
        $offer = $this->service->createOfferWithLines($lead, $lines);

        /* Assert */
        $this->assertInstanceOf(Offer::class, $offer);
        $this->assertNotNull($offer->external_id);
        $this->assertEquals(OfferStatus::inProgress()->getStatus(), $offer->status);
        $this->assertEquals($lead->client_id, $offer->client_id);
        $this->assertCount(2, $offer->invoiceLines);
    }

    #[Test]
    public function it_creates_offer_with_product_lines()
    {
        /* Arrange */
        $lead    = Lead::factory()->create();
        $product = Product::factory()->create();
        $lines   = [
            [
                'title'    => 'Product',
                'type'     => 'product',
                'price'    => 250.00,
                'quantity' => 1,
                'product'  => $product->external_id,
            ],
        ];

        /* Act */
        $offer = $this->service->createOfferWithLines($lead, $lines);

        /* Assert */
        $this->assertCount(1, $offer->invoiceLines);
        $this->assertEquals($product->id, $offer->invoiceLines->first()->product_id);
    }

    #[Test]
    public function it_adds_invoice_lines_with_default_quantity()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $lines = [
            [
                'title'    => 'Service',
                'type'     => 'service',
                'price'    => 100.00,
                'quantity' => 0, // Should default to 1
            ],
        ];

        /* Act */
        $offer = $this->service->createOfferWithLines($lead, $lines);

        /* Assert */
        $this->assertEquals(1, $offer->invoiceLines->first()->quantity);
    }

    #[Test]
    public function it_updates_invoice_lines()
    {
        /* Arrange */
        $offer = Offer::factory()->create();
        InvoiceLine::factory()->count(2)->create(['offer_id' => $offer->id]);

        $newLines = [
            [
                'title'    => 'Updated',
                'type'     => 'service',
                'price'    => 500.00,
                'quantity' => 1,
            ],
        ];

        /* Act */
        $this->service->updateInvoiceLinesFor($offer, $newLines);

        /* Assert */
        $this->assertCount(1, $offer->fresh()->invoiceLines);
        $this->assertEquals('Updated', $offer->fresh()->invoiceLines->first()->title);
    }

    #[Test]
    public function it_deletes_offer()
    {
        /* Arrange */
        $offer   = Offer::factory()->create();
        $offerId = $offer->id;

        /* Act */
        $result = $this->service->deleteOffer($offer);

        /* Assert */
        $this->assertTrue($result);
        $this->assertSoftDeleted('offers', ['id' => $offerId]);
    }

    #[Test]
    public function it_converts_price_to_cents()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $lines = [
            [
                'title'    => 'Service',
                'type'     => 'service',
                'price'    => 99.99,
                'quantity' => 1,
            ],
        ];

        /* Act */
        $offer = $this->service->createOfferWithLines($lead, $lines);

        /* Assert */
        $this->assertEquals(9999, $offer->invoiceLines->first()->price);
    }

    #[Test]
    public function it_throws_exception_for_missing_required_fields()
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $lines = [
            [
                'title' => 'Service',
                // Missing type, price, quantity
            ],
        ];
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required invoice line fields');

        /* Act */
        $this->service->createOfferWithLines($lead, $lines);

        /* Assert */
    }

    #[Test]
    public function it_converts_offer_to_invoice()
    {
        /* Arrange */
        $offer = Offer::factory()->create();
        InvoiceLine::factory()->count(2)->create(['offer_id' => $offer->id]);

        /* Act */
        $invoice = $this->service->convertToInvoice($offer);

        /* Assert */
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($offer->id, $invoice->offer_id);
        $this->assertNotNull($invoice->invoice_number);
        $this->assertCount(2, $invoice->invoiceLines);
    }

    #[Test]
    public function it_marks_offer_as_lost()
    {
        /* Arrange */
        $offer = Offer::factory()->create();

        /* Act */
        $result = $this->service->markAsLost($offer);

        /* Assert */
        $this->assertTrue($result);
        $this->assertEquals(OfferStatus::lost()->getStatus(), $offer->fresh()->status);
    }

    #[Test]
    public function it_finds_offer_by_external_id()
    {
        /* Arrange */
        $offer = Offer::factory()->create();

        /* Act */
        $found = $this->service->findByExternalId($offer->external_id);

        /* Assert */
        $this->assertNotNull($found);
        $this->assertEquals($offer->id, $found->id);
    }

    #[Test]
    public function it_returns_null_for_nonexistent_offer()
    {
        /* Arrange */

        /* Act */
        $found = $this->service->findByExternalId('nonexistent-id');

        /* Assert */
        $this->assertNull($found);
    }

    #[Test]
    public function it_creates_offer_for_lead_source(): void
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $lines = [['title' => 'Line 1', 'type' => 'service', 'price' => 10, 'quantity' => 1]];

        /* Act */
        $offer = $this->service->createForSource($lines, $lead->id, $lead->client_id, Lead::class);

        /* Assert */
        $this->assertEquals(Lead::class, $offer->source_type);
        $this->assertEquals($lead->id, $offer->source_id);
        $this->assertCount(1, $offer->invoiceLines);
    }

    #[Test]
    public function it_creates_offer_for_client_source(): void
    {
        /* Arrange */
        $client = \App\Models\Client::factory()->create();
        $lines  = [['title' => 'Line 1', 'type' => 'service', 'price' => 10, 'quantity' => 1]];

        /* Act */
        $offer = $this->service->createForSource($lines, $client->id, $client->id, \App\Models\Client::class);

        /* Assert */
        $this->assertEquals(\App\Models\Client::class, $offer->source_type);
        $this->assertEquals($client->id, $offer->client_id);
        $this->assertEquals($client->id, $offer->source_id);
        $this->assertCount(1, $offer->invoiceLines);
    }

    #[Test]
    public function it_rejects_an_invalid_offer_source_type(): void
    {
        /* Arrange */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->service->createForSource([], 1, 1, Offer::class);
    }

    #[Test]
    public function it_throws_validation_exception_when_line_product_does_not_exist(): void
    {
        /* Arrange */
        $lead  = Lead::factory()->create();
        $lines = [['title' => 'Line 1', 'type' => 'service', 'price' => 10, 'quantity' => 1, 'product' => 'missing-external-id']];

        /* Act */
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->service->createForSource($lines, $lead->id, $lead->client_id, Lead::class);
    }

    #[Test]
    public function it_replaces_invoice_lines_on_an_offer(): void
    {
        /* Arrange */
        $offer = Offer::factory()->create();
        InvoiceLine::factory()->create(['offer_id' => $offer->id]);
        $newLines = [['title' => 'New line', 'type' => 'service', 'price' => 20, 'quantity' => 1]];

        /* Act */
        $this->service->replaceInvoiceLines($offer, $newLines);

        /* Assert */
        $offer->refresh();
        $this->assertCount(1, $offer->invoiceLines);
        $this->assertEquals('New line', $offer->invoiceLines->first()->title);
    }

    #[Test]
    public function it_rejects_replacing_invoice_lines_with_a_missing_field(): void
    {
        /* Arrange */
        $offer = Offer::factory()->create();

        /* Act */
        $this->expectException(InvalidArgumentException::class);
        $this->service->replaceInvoiceLines($offer, [['type' => 'service', 'price' => 20, 'quantity' => 1]]);
    }
}

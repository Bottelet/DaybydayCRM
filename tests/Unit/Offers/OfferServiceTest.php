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

        /* Act & Assert */
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing required invoice line fields');
        $this->service->createOfferWithLines($lead, $lines);
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
        /* Act */
        $found = $this->service->findByExternalId('nonexistent-id');

        /* Assert */
        $this->assertNull($found);
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
}

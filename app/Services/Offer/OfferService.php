<?php

namespace App\Services\Offer;

use App\Enums\InvoiceStatus;
use App\Enums\OfferStatus;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Lead;
use App\Models\Offer;
use App\Models\Product;
use App\Services\InvoiceNumber\InvoiceNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class OfferService
{
    /**
     * Create a new offer for a Lead or Client source, with invoice lines.
     * Each line's product (if given) must resolve to a real Product -
     * matches the validation the controller previously did inline.
     *
     * @param array  $lines      Array of validated invoice line data
     * @param int    $sourceId   ID of the source record (Lead or Client)
     * @param int    $clientId   Client the offer belongs to
     * @param string $sourceType Lead::class or Client::class
     *
     * @throws InvalidArgumentException If the source type isn't Lead/Client
     * @throws ValidationException      If a line references a product that doesn't exist
     */
    public function createForSource(array $lines, int $sourceId, int $clientId, string $sourceType): Offer
    {
        if ( ! in_array($sourceType, [Lead::class, Client::class], true)) {
            throw new InvalidArgumentException('Invalid offer source type provided');
        }

        return DB::transaction(function () use ($lines, $sourceId, $clientId, $sourceType): Offer {
            $offer = Offer::query()->create([
                'status'      => OfferStatus::inProgress()->getStatus(),
                'client_id'   => $clientId,
                'external_id' => Uuid::uuid4()->toString(),
                'source_id'   => $sourceId,
                'source_type' => $sourceType,
            ]);

            foreach ($lines as $index => $line) {
                $productId = null;
                if (isset($line['product']) && $line['product']) {
                    $productId = Product::whereExternalId($line['product'])->value('id');

                    if ( ! $productId) {
                        throw ValidationException::withMessages([
                            $index . '.product' => __('Selected product was not found.'),
                        ]);
                    }
                }

                $invoiceLine = InvoiceLine::make([
                    'title'      => $line['title'],
                    'type'       => $line['type'],
                    'quantity'   => $line['quantity'] ?: 1,
                    'comment'    => $line['comment'] ?? null,
                    'price'      => $line['price'] * 100,
                    'product_id' => $productId,
                ]);
                $offer->invoiceLines()->save($invoiceLine);
            }

            return $offer;
        });
    }

    /**
     * Replace an offer's invoice lines wholesale - matches the controller's
     * previous inline behavior: missing required fields on any line abort
     * the whole replacement (nothing is deleted/added) via an exception the
     * controller maps to its existing 422 response.
     *
     * @throws InvalidArgumentException If any line is missing a required field
     */
    public function replaceInvoiceLines(Offer $offer, array $lines): void
    {
        foreach ($lines as $line) {
            if (empty($line['title']) || empty($line['type']) || empty($line['price']) || empty($line['quantity'])) {
                throw new InvalidArgumentException('missing fields');
            }
        }

        $offer->invoiceLines()->forceDelete();

        foreach ($lines as $line) {
            $product     = isset($line['product']) && $line['product'] ? Product::whereExternalId($line['product'])->first() : null;
            $invoiceLine = InvoiceLine::make([
                'title'      => $line['title'],
                'type'       => $line['type'],
                'quantity'   => $line['quantity'] ?: 1,
                'comment'    => $line['comment'] ?? null,
                'price'      => $line['price'] * 100,
                'product_id' => $product?->id,
            ]);
            $offer->invoiceLines()->save($invoiceLine);
        }
    }

    /**
     * Create a new offer with invoice lines.
     *
     * @param Lead  $lead  The lead the offer is for
     * @param array $lines Array of invoice line data
     *
     * @return Offer The created offer
     *
     * @throws InvalidArgumentException If line data is invalid
     */
    public function createOfferWithLines(Lead $lead, array $lines): Offer
    {
        // Create the offer
        $offer = Offer::query()->create([
            'status'      => OfferStatus::inProgress()->getStatus(),
            'client_id'   => $lead->client_id,
            'external_id' => Uuid::uuid4()->toString(),
            'source_id'   => $lead->id,
            'source_type' => Lead::class,
        ]);

        // Add invoice lines
        $this->addInvoiceLinesTo($offer, $lines);

        return $offer;
    }

    /**
     * Add invoice lines to an offer.
     *
     * @param Offer $offer The offer
     * @param array $lines Array of invoice line data
     *
     * @throws InvalidArgumentException If line data is invalid
     */
    public function addInvoiceLinesTo(Offer $offer, array $lines): void
    {
        foreach ($lines as $line) {
            // Validate required fields
            if ( ! isset($line['title']) || ! isset($line['type']) || ! isset($line['price']) || ! isset($line['quantity'])) {
                throw new InvalidArgumentException('Missing required invoice line fields: title, type, price, quantity');
            }

            // Resolve product if provided
            $productId = null;
            if (isset($line['product']) && $line['product']) {
                $product = Product::whereExternalId($line['product'])->first();
                if ($product) {
                    $productId = $product->id;
                }
            } elseif (isset($line['product_id']) && $line['product_id']) {
                $productId = $line['product_id'];
            }

            // Create invoice line
            $invoiceLine = InvoiceLine::make([
                'title'      => $line['title'],
                'type'       => $line['type'],
                'quantity'   => $line['quantity'] ?: 1,
                'comment'    => $line['comment'] ?? null,
                'price'      => (int) ($line['price'] * 100), // Convert to cents
                'product_id' => $productId,
            ]);

            $offer->invoiceLines()->save($invoiceLine);
        }
    }

    /**
     * Update invoice lines for an offer.
     *
     * @param Offer $offer The offer
     * @param array $lines Array of invoice line data
     *
     * @throws InvalidArgumentException If line data is invalid
     */
    public function updateInvoiceLinesFor(Offer $offer, array $lines): void
    {
        // Delete existing lines
        $offer->invoiceLines()->forceDelete();

        // Add new lines
        $this->addInvoiceLinesTo($offer, $lines);
    }

    /**
     * Convert an offer to an invoice (mark as won).
     *
     * @param Offer $offer The offer to convert
     *
     * @return Invoice The created invoice
     */
    public function convertToInvoice(Offer $offer): Invoice
    {
        // Mark offer as won
        $offer->setAsWon();

        // Create invoice from offer
        $invoice                 = Invoice::query()->create($offer->toArray());
        $invoice->offer_id       = $offer->id;
        $invoice->invoice_number = app(InvoiceNumberService::class)->setNextInvoiceNumber();
        $invoice->status         = InvoiceStatus::draft()->getStatus();
        $invoice->save();

        // Copy invoice lines from offer to invoice
        $lines    = $offer->invoiceLines;
        $newLines = collect();
        foreach ($lines as $invoiceLine) {
            $invoiceLine->offer_id = null;
            $newLines->push(InvoiceLine::make($invoiceLine->toArray()));
        }

        $invoice->invoiceLines()->saveMany($newLines);

        return $invoice;
    }

    /**
     * Mark an offer as lost.
     *
     * @param Offer $offer The offer to mark as lost
     *
     * @return bool Success
     */
    public function markAsLost(Offer $offer): bool
    {
        $offer->setAsLost();

        return true;
    }

    /**
     * Get offer by external ID.
     *
     * @param string $externalId The external ID
     *
     * @return Offer|null The offer or null if not found
     */
    public function findByExternalId(string $externalId): ?Offer
    {
        return Offer::where('external_id', $externalId)->first();
    }

    /**
     * Delete an offer.
     *
     * @param Offer $offer The offer to delete
     *
     * @return bool Success
     */
    public function deleteOffer(Offer $offer): bool
    {
        return (bool) $offer->delete();
    }
}

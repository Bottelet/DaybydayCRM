<?php

namespace App\Services\InvoiceLine;

use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Models\Product;
use InvalidArgumentException;
use Ramsey\Uuid\Uuid;

class InvoiceLineService
{
    /**
     * Create a new invoice line.
     *
     * @param Invoice     $invoice   The invoice
     * @param string      $title     Line title
     * @param string      $type      Line type (product, service, etc.)
     * @param int         $quantity  Quantity
     * @param float       $price     Price per unit (in decimal)
     * @param string|null $comment   Optional comment
     * @param int|null    $productId Optional product ID
     *
     * @return InvoiceLine The created line
     *
     * @throws InvalidArgumentException If invoice cannot be updated
     */
    public function createLine(
        Invoice $invoice,
        string $title,
        string $type,
        int $quantity,
        float $price,
        ?string $comment = null,
        ?int $productId = null
    ): InvoiceLine {
        // Verify invoice can be updated
        if ( ! $invoice->canUpdateInvoice()) {
            throw new InvalidArgumentException('Cannot add lines to a sent invoice');
        }

        return InvoiceLine::query()->create([
            'external_id' => Uuid::uuid4()->toString(),
            'title'       => $title,
            'comment'     => $comment,
            'quantity'    => $quantity,
            'type'        => $type,
            'price'       => (int) ($price * 100), // Convert to cents
            'invoice_id'  => $invoice->id,
            'product_id'  => $productId,
        ]);
    }

    /**
     * Create an invoice line from product external ID.
     *
     * @param Invoice     $invoice           The invoice
     * @param string      $title             Line title
     * @param string      $type              Line type
     * @param int         $quantity          Quantity
     * @param float       $price             Price per unit
     * @param string|null $comment           Optional comment
     * @param string|null $productExternalId Optional product external ID
     *
     * @return InvoiceLine The created line
     */
    public function createLineFromProduct(
        Invoice $invoice,
        string $title,
        string $type,
        int $quantity,
        float $price,
        ?string $comment = null,
        ?string $productExternalId = null
    ): InvoiceLine {
        $productId = null;
        if ($productExternalId) {
            $product = Product::whereExternalId($productExternalId)->first();
            if ($product) {
                $productId = $product->id;
            }
        }

        return $this->createLine($invoice, $title, $type, $quantity, $price, $comment, $productId);
    }

    /**
     * Delete an invoice line.
     *
     * @param InvoiceLine $line The line to delete
     *
     * @return bool Success
     *
     * @throws InvalidArgumentException If invoice cannot be updated
     */
    public function deleteLine(InvoiceLine $line): bool
    {
        if ( ! $line->invoice->canUpdateInvoice()) {
            throw new InvalidArgumentException('Cannot delete lines from a sent invoice');
        }

        return (bool) $line->delete();
    }

    /**
     * Update an invoice line.
     *
     * @param InvoiceLine $line The line to update
     * @param array       $data Update data
     *
     * @return bool Success
     *
     * @throws InvalidArgumentException If invoice cannot be updated
     */
    public function updateLine(InvoiceLine $line, array $data): bool
    {
        if ( ! $line->invoice->canUpdateInvoice()) {
            throw new InvalidArgumentException('Cannot update lines on a sent invoice');
        }

        // Convert price to cents if provided
        if (isset($data['price']) && is_float($data['price'])) {
            $data['price'] = (int) ($data['price'] * 100);
        }

        return $line->update($data);
    }

    /**
     * Get invoice line by external ID.
     *
     * @param string $externalId The external ID
     *
     * @return InvoiceLine|null The line or null if not found
     */
    public function findByExternalId(string $externalId): ?InvoiceLine
    {
        return InvoiceLine::where('external_id', $externalId)->first();
    }

    /**
     * Get all lines for an invoice.
     *
     * @param Invoice $invoice The invoice
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLinesForInvoice(Invoice $invoice)
    {
        return $invoice->invoiceLines()->get();
    }

    /**
     * Calculate total for invoice lines.
     *
     * @param Invoice $invoice The invoice
     *
     * @return int Total in cents
     */
    public function calculateTotal(Invoice $invoice): int
    {
        return (int) $invoice->invoiceLines()
            ->selectRaw('SUM(price * quantity) as total')
            ->first()
            ->total ?? 0;
    }
}

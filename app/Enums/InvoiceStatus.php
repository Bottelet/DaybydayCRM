<?php

namespace App\Enums;

use Exception;

class InvoiceStatus
{
    private const DRAFT = 'draft';

    private const CLOSED = 'closed';

    private const SENT = 'sent';

    private const UNPAID = 'unpaid';

    private const PARTIAL_PAID = 'partial_paid';

    private const PAID = 'paid';

    private const OVERPAID = 'overpaid';

    /**
     * @var InvoiceStatus[]
     */
    private static $values = null;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $displayValue;

    public function __construct(string $status, ?string $displayValue = null)
    {
        $this->status       = $status;
        $this->displayValue = $displayValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->status;
    }

    /**
     * @throws Exception
     */
    public static function fromStatus(string $status): self
    {
        foreach (self::values() as $invoiceStatus) {
            if ($invoiceStatus->getStatus() === $status) {
                return $invoiceStatus;
            }
        }
        throw new Exception('Unknown invoice status: ' . $status);
    }

    /**
     * @param string $displayValue
     *
     * @return InvoiceStatus
     *
     * @throws Exception
     */
    public static function fromDisplayValue($displayValue)
    {
        foreach (self::values() as $invoiceStatus) {
            if ($invoiceStatus->getDisplayValue() === $displayValue) {
                return $invoiceStatus;
            }
        }
        throw new Exception('Unknown invoice status display value: ' . $displayValue);
    }

    /**
     * @return InvoiceStatus[]
     */
    public static function values(): array
    {
        if (null === self::$values) {
            self::$values = [
                self::DRAFT        => new self(self::DRAFT, 'Draft'),
                self::CLOSED       => new self(self::CLOSED, 'Closed'),
                self::SENT         => new self(self::SENT, 'Sent'),
                self::UNPAID       => new self(self::UNPAID, 'Unpaid'),
                self::PARTIAL_PAID => new self(self::PARTIAL_PAID, 'Partially paid'),
                self::PAID         => new self(self::PAID, 'Paid'),
                self::OVERPAID     => new self(self::OVERPAID, 'Overpaid'),
            ];
        }

        return self::$values;
    }

    public static function draft(): self
    {
        return self::values()[self::DRAFT];
    }

    public static function closed(): self
    {
        return self::values()[self::CLOSED];
    }

    public static function sent(): self
    {
        return self::values()[self::SENT];
    }

    public static function unpaid(): self
    {
        return self::values()[self::UNPAID];
    }

    public static function partialPaid(): self
    {
        return self::values()[self::PARTIAL_PAID];
    }

    public static function paid(): self
    {
        return self::values()[self::PAID];
    }

    public static function overpaid(): self
    {
        return self::values()[self::OVERPAID];
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }
}

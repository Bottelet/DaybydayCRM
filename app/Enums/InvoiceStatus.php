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

    public function __construct(string $status, string $displayValue = null)
    {
        $this->status = $status;
        $this->displayValue = $displayValue;
    }

    /**
     * @param string $status
     * @return InvoiceStatus
     * @throws Exception
     */
    public static function fromStatus(string $status): InvoiceStatus
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
     * @return InvoiceStatus
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
        if (is_null(self::$values)) {
            self::$values = [
                self::DRAFT => new InvoiceStatus(self::DRAFT, 'Draft'),
                self::CLOSED => new InvoiceStatus(self::CLOSED, 'Closed'),
                self::SENT => new InvoiceStatus(self::SENT, 'Sent'),
                self::UNPAID => new InvoiceStatus(self::UNPAID, 'Unpaid'),
                self::PARTIAL_PAID => new InvoiceStatus(self::PARTIAL_PAID, 'Partially paid'),
                self::PAID => new InvoiceStatus(self::PAID, 'Paid'),
                self::OVERPAID => new InvoiceStatus(self::OVERPAID, 'Overpaid'),
            ];
        }
        return self::$values;
    }

    /**
     * @return InvoiceStatus
     */
    public static function draft(): InvoiceStatus
    {
        return self::values()[self::DRAFT];
    }

    /**
     * @return InvoiceStatus
     */
    public static function closed(): InvoiceStatus
    {
        return self::values()[self::CLOSED];
    }

    /**
     * @return InvoiceStatus
     */
    public static function sent(): InvoiceStatus
    {
        return self::values()[self::SENT];
    }

    /**
     * @return InvoiceStatus
     */
    public static function unpaid(): InvoiceStatus
    {
        return self::values()[self::UNPAID];
    }

    /**
     * @return InvoiceStatus
     */
    public static function partialPaid(): InvoiceStatus
    {
        return self::values()[self::PARTIAL_PAID];
    }

    /**
     * @return InvoiceStatus
     */
    public static function paid(): InvoiceStatus
    {
        return self::values()[self::PAID];
    }

    /**
     * @return InvoiceStatus
     */
    public static function overpaid(): InvoiceStatus
    {
        return self::values()[self::OVERPAID];
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->status;
    }
}

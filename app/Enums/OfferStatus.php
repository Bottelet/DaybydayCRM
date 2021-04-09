<?php

namespace App\Enums;

use Exception;

class OfferStatus
{
    private const IN_PROGRESS = 'in-progress';
    private const LOST = 'lost';
    private const WON = 'won';

    /**
     * @var OfferStatus[]
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
     * @return OfferStatus
     * @throws Exception
     */
    public static function fromStatus(string $status): OfferStatus
    {
        foreach (self::values() as $offerStatus) {
            if ($offerStatus->getStatus() === $status) {
                return $offerStatus;
            }
        }
        throw new Exception('Unknown invoice status: ' . $status);
    }

    /**
     * @param string $displayValue
     * @return OfferStatus
     * @throws Exception
     */
    public static function fromDisplayValue($displayValue)
    {
        foreach (self::values() as $offerStatus) {
            if ($offerStatus->getDisplayValue() === $displayValue) {
                return $offerStatus;
            }
        }
        throw new Exception('Unknown invoice status display value: ' . $displayValue);
    }

    /**
     * @return OfferStatus[]
     */
    public static function values(): array
    {
        if (is_null(self::$values)) {
            self::$values = [
                self::IN_PROGRESS => new OfferStatus(self::IN_PROGRESS, 'In-progress'),
                self::LOST => new OfferStatus(self::LOST, 'Lost'),
                self::WON => new OfferStatus(self::WON, 'Won'),
            ];
        }
        return self::$values;
    }

    /**
     * @return OfferStatus
     */
    public static function inProgress(): OfferStatus
    {
        return self::values()[self::IN_PROGRESS];
    }

    /**
     * @return OfferStatus
     */
    public static function lost(): OfferStatus
    {
        return self::values()[self::LOST];
    }

    /**
     * @return OfferStatus
     */
    public static function won(): OfferStatus
    {
        return self::values()[self::WON];
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

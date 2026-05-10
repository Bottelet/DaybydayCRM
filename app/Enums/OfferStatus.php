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
        foreach (self::values() as $offerStatus) {
            if ($offerStatus->getStatus() === $status) {
                return $offerStatus;
            }
        }
        throw new Exception('Unknown invoice status: ' . $status);
    }

    /**
     * @param string $displayValue
     *
     * @return OfferStatus
     *
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
        if (null === self::$values) {
            self::$values = [
                self::IN_PROGRESS => new self(self::IN_PROGRESS, 'In-progress'),
                self::LOST        => new self(self::LOST, 'Lost'),
                self::WON         => new self(self::WON, 'Won'),
            ];
        }

        return self::$values;
    }

    public static function inProgress(): self
    {
        return self::values()[self::IN_PROGRESS];
    }

    public static function lost(): self
    {
        return self::values()[self::LOST];
    }

    public static function won(): self
    {
        return self::values()[self::WON];
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

<?php

namespace App\Enums;

use InvalidArgumentException;

class AbsenceReason
{
    private const VACATION = 'vacation';

    private const VACATION_DAY = 'vacation_day';

    private const TIME_OFF = 'time_off';

    private const FLEXTIME = 'flextime';

    private const SICK_LEAVE = 'sick_leave';

    private const PERSONAL_LEAVE = 'personal_leave';

    private const TIME_OFF_IN_LIEU = 'time_off_in_lieu';

    private const OTHER = 'other';

    /**
     * @var AbsenceReason[]
     */
    private static $values = null;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $displayValue;

    public function __construct(string $reason, ?string $displayValue = null)
    {
        $this->reason       = $reason;
        $this->displayValue = $displayValue;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->reason;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function fromStatus(string $reason): self
    {
        foreach (self::values() as $absenceReason) {
            if ($absenceReason->getReason() === $reason) {
                return $absenceReason;
            }
        }
        throw new InvalidArgumentException('Unknown absence reason: ' . $reason);
    }

    /**
     * @param string $displayValue
     *
     * @return AbsenceReason
     *
     * @throws InvalidArgumentException
     */
    public static function fromDisplayValue($displayValue)
    {
        foreach (self::values() as $absenceReason) {
            if ($absenceReason->getDisplayValue() === $displayValue) {
                return $absenceReason;
            }
        }
        throw new InvalidArgumentException('Unknown absence reason display value: ' . $displayValue);
    }

    /**
     * @return AbsenceReason[]
     */
    public static function values(): array
    {
        if (null === self::$values) {
            self::$values = [
                self::SICK_LEAVE       => new self(self::SICK_LEAVE, 'Sick leave'),
                self::PERSONAL_LEAVE   => new self(self::PERSONAL_LEAVE, 'Personal leave'),
                self::VACATION         => new self(self::VACATION, 'Vacation'),
                self::VACATION_DAY     => new self(self::VACATION_DAY, 'Vacation day'),
                self::TIME_OFF         => new self(self::TIME_OFF, 'Time off'),
                self::TIME_OFF_IN_LIEU => new self(self::TIME_OFF, 'Time off in lieu'),
                self::FLEXTIME         => new self(self::FLEXTIME, 'Flextime'),
                self::OTHER            => new self(self::OTHER, 'Other'),
            ];
        }

        return self::$values;
    }

    public static function vacation(): self
    {
        return self::values()[self::VACATION];
    }

    public static function vacationDay(): self
    {
        return self::values()[self::VACATION_DAY];
    }

    public static function sickLeave(): self
    {
        return self::values()[self::SICK_LEAVE];
    }

    public static function timeOff(): self
    {
        return self::values()[self::TIME_OFF];
    }

    public static function timeOffInLieu(): self
    {
        return self::values()[self::TIME_OFF_IN_LIEU];
    }

    public static function personalLeave(): self
    {
        return self::values()[self::PERSONAL_LEAVE];
    }

    public static function flextime(): self
    {
        return self::values()[self::FLEXTIME];
    }

    public static function other(): self
    {
        return self::values()[self::OTHER];
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }
}

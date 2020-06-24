<?php

namespace App\Enums;

use Exception;
use Illuminate\Validation\Rule;

class PaymentSource
{
    private const BANK = 'bank';
    private const CASH = 'cash';
    private const INTERCOMPANY = 'intercompany'; // Mellemregning
    private const EXPENSES = 'expenses'; //Udlæg

    /**
     * @var PaymentSource[]
     */
    private static $values = null;
    /**
     * @var string
     */
    private $source;
    /**
     * @var string
     */
    private $displayValue;

    public function __construct(string $source, string $displayValue = null)
    {
        $this->source = $source;
        $this->displayValue = $displayValue;
    }

    /**
     * @param string $source
     * @return PaymentSource
     * @throws Exception
     */
    public static function fromSource(string $source): PaymentSource
    {
        foreach (self::values() as $paymentSource) {
            if ($paymentSource->getSource() === $source) {
                return $paymentSource;
            }
        }
        throw new Exception('Unknown control status: ' . $source);
    }

    /**
     * @param string $displayValue
     * @return PaymentSource
     * @throws Exception
     */
    public static function fromDisplayValue($displayValue)
    {
        foreach (self::values() as $paymentSource) {
            if ($paymentSource->getDisplayValue() === $displayValue) {
                return $paymentSource;
            }
        }
        throw new Exception('Unknown control status display value: ' . $displayValue);
    }

    /**
     * @return PaymentSource[]
     */
    public static function values(): array
    {
        if (is_null(self::$values)) {
            self::$values = [
                self::BANK => new PaymentSource(self::BANK, 'Bank'),
                self::CASH => new PaymentSource(self::CASH, 'Cash'),
                self::EXPENSES => new PaymentSource(self::EXPENSES, 'Expenses'),
                self::INTERCOMPANY => new PaymentSource(self::INTERCOMPANY, 'Intercompany')
            ];
        }
        return self::$values;
    }

    /**
     * @return PaymentSource
     */
    public static function bank(): PaymentSource
    {
        return self::values()[self::BANK];
    }

    /**
     * @return PaymentSource
     */
    public static function cash(): PaymentSource
    {
        return self::values()[self::CASH];
    }

    /**
     * @return PaymentSource
     */
    public static function intercompany(): PaymentSource
    {
        return self::values()[self::INTERCOMPANY];
    }

    /**
     * @return PaymentSource
     */
    public static function expenses(): PaymentSource
    {
        return self::values()[self::EXPENSES];
    }

    public static function validationRules()
    {
        $values = array_column(self::values(), "source");
        return Rule::in($values);
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
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
        return (string) $this->source;
    }
}

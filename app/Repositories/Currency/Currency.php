<?php
namespace App\Repositories\Currency;

class Currency
{
    protected $code;
    /**
     * Currency symbol.
     *
     * @var string
     */
    protected $symbol;
    /**
     * Currency precision (number of decimals).
     *
     * @var int
     */
    protected $precision;
    /**
     * Currency title.
     *
     * @var string
     */
    protected $title;
    /**
     * Currency thousand separator.
     *
     * @var string
     */
    protected $thousandSeparator;
    /**
     * Currency decimal separator.
     *
     * @var string
     */
    protected $decimalSeparator;

    protected $vatPercentage;
    /**
     * Currency symbol placement.
     *
     * @var string (front|after) currency
     */
    protected $symbolPlacement;
    private static $currencies = [
        'DKK' => [
            'code'              => 'DKK',
            'title'             => 'Danish Krone',
            'symbol'            => 'kr.',
            'precision'         => 2,
            'thousandSeparator' => '.',
            'decimalSeparator'  => ',',
            'symbolPlacement'   => 'after',
            'vatPercentage'     => 2500,
        ],
        'USD' => [
            'title'             => 'US Dollar',
            'code'              => 'USD',
            'symbol'            => '$',
            'precision'         => 2,
            'thousandSeparator' => '.',
            'decimalSeparator'  => ',',
            'symbolPlacement'   => 'before',
            'vatPercentage'     => 725,
        ],
        'EUR' => [
            'title'             => 'Euro',
            'code'              => 'EUR',
            'symbol'            => '€',
            'precision'         => 2,
            'thousandSeparator' => '.',
            'decimalSeparator'  => ',',
            'symbolPlacement'   => 'before',
            'vatPercentage'     => 2000,
        ]
    ];
    public function __construct($code)
    {
        $currency = $this->getCurrency($code);
        foreach ($currency as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    protected function defaultCurrency()
    {
        return $this->getCurrency("EUR");
    }
    /**
     * Get currency ISO-4217 code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getVatPercentage()
    {
        return $this->vatPercentage;
    }
    /**
     * Get currency symbol.
     *
     * @return string
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
    /**
     * Get currency precision.
     *
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }
    /**
     * @param integer $precision
     * @return $this
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
        return $this;
    }
    /**
     * Get currency title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
    /**
     * Get currency thousand separator.
     *
     * @return string
     */
    public function getThousandSeparator()
    {
        return $this->thousandSeparator;
    }
    /**
     * @param string $separator
     * @return $this
     */
    public function setThousandSeparator($separator)
    {
        $this->thousandSeparator = $separator;
        return $this;
    }
    /**
     * Get currency decimal separator.
     *
     * @return string
     */
    public function getDecimalSeparator()
    {
        return $this->decimalSeparator;
    }
    /**
     * @param string $separator
     * @return $this
     */
    public function setDecimalSeparator($separator)
    {
        $this->decimalSeparator = $separator;
        return $this;
    }
    /**
     * Get currency symbol placement.
     *
     * @return string
     */
    public function getSymbolPlacement()
    {
        return $this->symbolPlacement;
    }
    /**
     * @param string $placement [before|after]
     * @return $this
     */
    public function setSymbolPlacement($placement)
    {
        $this->symbolPlacement = $placement;
        return $this;
    }
    public function toArray()
    {
        return [
            'code'              => $this->getCode(),
            'title'             => $this->getTitle(),
            'symbol'            => $this->getSymbol(),
            'precision'         => $this->getPrecision(),
            'thousandSeparator' => $this->getThousandSeparator(),
            'decimalSeparator'  => $this->getDecimalSeparator(),
            'symbolPlacement'   => $this->getSymbolPlacement(),
        ];
    }
    /**
     * Get all currencies.
     *
     * @return array
     */
    public static function getAllCurrencies()
    {
        return self::$currencies;
    }
    /**
     * Get currency.
     *
     * @access protected
     * @return array
     */
    public function getCurrency($code)
    {
        return isset(self::$currencies[$code]) ? self::$currencies[$code] : $this->defaultCurrency();
    }
    /**
     * Check currency existence (within the class)
     *
     * @access protected
     * @return bool
     */
    public function hasCurrency($code)
    {
        return isset(self::$currencies[$code]);
    }
}

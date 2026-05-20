<?php

namespace App\Enums;

use Exception;

class Country
{
    /**
     * @var Country[]
     */
    private static $values = null;

    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $displayValue;

    private $format;

    private $currencyCode;

    private $language;

    private $phoneCode;

    public function __construct(string $code, array $parameters)
    {
        $this->code         = $code;
        $this->displayValue = data_get($parameters, 'displayValue');
        $this->currencyCode = data_get($parameters, 'currencyCode');
        $this->language     = data_get($parameters, 'language');
        $this->phoneCode    = data_get($parameters, 'phoneCode');
        $this->format       = data_get($parameters, 'format');
    }

    /**
     * @throws Exception
     */
    public static function fromCode(string $code): self
    {
        foreach (self::values() as $country) {
            if ($country->getCode() === $code) {
                return $country;
            }
        }

        return self::$values['OT'];
    }

    /**
     * @return Country[]
     */
    public static function values(): array
    {
        if (null === self::$values) {
            self::$values = [
                'DK' => new self('DK', [
                    'displayValue' => 'Denmark',
                    'currencyCode' => 'DKK',
                    'language'     => 'Danish',
                    'phoneCode'    => '+45',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'DE' => new self('DE', [
                    'displayValue' => 'Germany',
                    'currencyCode' => 'EUR',
                    'language'     => 'German',
                    'phoneCode'    => '+49',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'SE' => new self('SE', [
                    'displayValue' => 'Sweden',
                    'currencyCode' => 'SEK',
                    'language'     => 'Swedish',
                    'phoneCode'    => '+49',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'GB' => new self('GB', [
                    'displayValue' => 'United Kingdom',
                    'currencyCode' => 'EUR',
                    'language'     => 'English',
                    'phoneCode'    => '+44',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'FR' => new self('FR', [
                    'displayValue' => 'France',
                    'currencyCode' => 'EUR',
                    'language'     => 'French',
                    'phoneCode'    => '+33',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'US' => new self('US', [
                    'displayValue' => 'United States',
                    'currencyCode' => 'USD',
                    'language'     => 'English',
                    'phoneCode'    => '+1',
                    'format'       => [
                        'frontendDate'               => 'mm/dd/yyyy',
                        'frontendTime'               => 'h:i a',
                        'momentjsDayAndDateWithText' => 'MMMM D ddd',
                        'momentJsTime'               => 'h:mm a',
                        'carbonDate'                 => 'm/d/Y',
                        'carbonTime'                 => 'g:i A',
                        'carbonFullDateWithText'     => 'F d, Y g:i A',
                        'carbonDateWithText'         => 'F d, Y',
                    ],
                ]),
                'RU' => new self('RU', [
                    'displayValue' => 'Russia',
                    'currencyCode' => 'RUB',
                    'language'     => 'Russian',
                    'phoneCode'    => '+71',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'H:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'ES' => new self('ES', [
                    'displayValue' => 'Spain',
                    'currencyCode' => 'EUR',
                    'language'     => 'Spanish',
                    'phoneCode'    => '+34',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'IN' => new self('IN', [
                    'displayValue' => 'India',
                    'currencyCode' => 'INR',
                    'language'     => 'Hindi',
                    'phoneCode'    => '+91',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'h:i A',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'g:i A',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
                'OT' => new self('OT', [
                    'displayValue' => 'Other',
                    'currencyCode' => 'EUR',
                    'language'     => 'English',
                    'phoneCode'    => '+44',
                    'format'       => [
                        'frontendDate'               => 'dd/mm/yyyy',
                        'frontendTime'               => 'HH:i',
                        'momentjsDayAndDateWithText' => 'ddd D MMMM',
                        'momentJsTime'               => 'HH:mm',
                        'carbonDate'                 => 'd/m/Y',
                        'carbonTime'                 => 'H:i',
                        'carbonFullDateWithText'     => 'd, F Y H:i',
                        'carbonDateWithText'         => 'd, F Y',
                    ],
                ]),
            ];
        }

        return self::$values;
    }

    public function getDisplayValue(): string
    {
        return $this->displayValue;
    }

    /**
     * @return mixed
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return mixed
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return mixed
     */
    public function getPhoneCode()
    {
        return $this->phoneCode;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}

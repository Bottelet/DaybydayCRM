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
        $this->code = $code;
        $this->displayValue = data_get($parameters, "displayValue");
        $this->currencyCode = data_get($parameters, "currencyCode");
        $this->language = data_get($parameters, "language");
        $this->phoneCode = data_get($parameters, "phoneCode");
        $this->format = data_get($parameters, "format");
    }

    /**
     * @return string
     */
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

    /**
     * @param string $code
     * @return Country
     * @throws Exception
     */
    public static function fromCode(string $code): Country
    {
        foreach (self::values() as $country) {
            if ($country->getCode() === $code) {
                return $country;
            }
        }
        return self::$values["OT"];
    }

    /**
     * @return Country[]
     */
    public static function values(): array
    {
        if (is_null(self::$values)) {
            self::$values = [
                "DK" => new Country("DK", [
                    "displayValue" => "Denmark",
                    "currencyCode" => "DKK",
                    "language" => "Danish",
                    "phoneCode" => "+45",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
                "DE" => new Country("DE", [
                    "displayValue" => "Germany",
                    "currencyCode" => "EUR",
                    "language" => "German",
                    "phoneCode" => "+49",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]

                ]),
                "SE" => new Country("SE", [
                    "displayValue" => "Sweden",
                    "currencyCode" => "SEK",
                    "language" => "Swedish",
                    "phoneCode" => "+49",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]

                ]),
                "GB"=> new Country("GB", [
                    "displayValue" => "United Kingdom",
                    "currencyCode" => "EUR",
                    "language" => "English",
                    "phoneCode" => "+44",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
                "FR"=> new Country("FR", [
                    "displayValue" => "France",
                    "currencyCode" => "EUR",
                    "language" => "French",
                    "phoneCode" => "+33",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
                "US"=> new Country("US", [
                    "displayValue" => "United States",
                    "currencyCode" => "USD",
                    "language" => "English",
                    "phoneCode" => "+1",
                    "format" => [
                        "frontendDate" => "mm/dd/yyyy",
                        "frontendTime" => "h:i a",
                        "momentjsDayAndDateWithText" => "MMMM D ddd",
                        "momentJsTime" => "h:mm a",
                        "carbonDate" => "m/d/Y",
                        "carbonTime" => "g:i A",
                        "carbonFullDateWithText" => "F d, Y g:i A",
                        "carbonDateWithText" => "F d, Y"

                    ]
                ]),
                "RU"=> new Country("RU", [
                    "displayValue" => "Russia",
                    "currencyCode" => "RUB",
                    "language" => "Russian",
                    "phoneCode" => "+71",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "H:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
                "ES" => new Country("ES", [
                    "displayValue" => "Spain",
                    "currencyCode" => "EUR",
                    "language" => "Spanish",
                    "phoneCode" => "+34",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
                "IN" => new Country("IN", [
                    "displayValue" => "India",
                    "currencyCode" => "INR",
                    "language" => "Hindi",
                    "phoneCode" => "+91",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "h:i A",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "g:i A",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
                "OT" => new Country("OT", [
                    "displayValue" => "Other",
                    "currencyCode" => "EUR",
                    "language" => "English",
                    "phoneCode" => "+44",
                    "format" => [
                        "frontendDate" => "dd/mm/yyyy",
                        "frontendTime" => "HH:i",
                        "momentjsDayAndDateWithText" => "ddd D MMMM",
                        "momentJsTime" => "HH:mm",
                        "carbonDate" => "d/m/Y",
                        "carbonTime" => "H:i",
                        "carbonFullDateWithText" => "d, F Y H:i",
                        "carbonDateWithText" => "d, F Y"

                    ]
                ]),
            ];
        }
        return self::$values;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}

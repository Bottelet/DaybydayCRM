<?php

namespace App\Repositories\Format;

use App\Enums\Country;
use App\Models\Setting;

class GetDateFormat
{
    private $format;

    const CACHE_KEY = "country_date_format";

    public function __construct()
    {
        //if (!cache(self::CACHE_KEY)){
            $this->format = Country::fromCode(Setting::first()->country)->getFormat();
            cache()->set("country_date_format", $this->format);
        //}

        //$this->format = cache(self::CACHE_KEY);
        
    }

    public function getAllDateFormats()
    {
        return [
            'frontend_date' => $this->getFrontendDate(),
            'frontend_time' => $this->getFrontendTime(),
            'carbon_date' => $this->getCarbonDate(),
            'carbon_time' => $this->getCarbonTime(),
            'carbon_full_date_with_text' => $this->getFrontendDate(),
            'carbon_date_with_text' => $this->getFrontendDate(),
            'momentjs_day_and_date_with_text' => $this->getMomentDateWithText(),
            'momentjs_time' => $this->getMomentTime(),
        ];
    }

    public function getFrontendDate()
    {
        return $this->format["frontendDate"];
    }

    public function getFrontendTime()
    {
        return $this->format["frontendTime"];
    }

    public function getCarbonTime()
    {
        return $this->format["carbonTime"];
    }

    public function getCarbonDate()
    {
        return $this->format["carbonDate"];
    }

    public function getCarbonFullDateWithText()
    {
        return $this->format["carbonFullDateWithText"];
    }

    public function getCarbonDateWithText()
    {
        return $this->format["carbonDateWithText"];
    }

    public function getMomentDateWithText()
    {
        return $this->format["momentjsDayAndDateWithText"];
    }

    public function getMomentTime()
    {
        return $this->format["momentJsTime"];
    }
}

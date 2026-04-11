<?php

namespace App\Repositories\Tax;

use App\Models\Setting;

class Tax
{
    /**
     * @var int
     */
    private $vatRate;

    /**
     * @var int
     */
    private $multipleVatRate;

    /**
     * Create a new Tax Rate
     *
     * @return void
     */
    public function __construct()
    {
        $this->vatRate = $this->integerToVatRate();
        $this->multipleVatRate = 1 + $this->vatRate;
    }

    /**
     * Return the Tax Rate as a float
     *
     * @return int
     */
    public function vatRate(): float
    {
        return $this->vatRate;
    }

    public function multipleVatRate(): float
    {
        return $this->multipleVatRate;
    }

    public function percentage()
    {
        $setting = Setting::select('vat')->first();

        return ($setting ? $setting->vat : 21) / 100;
    }

    private function integerToVatRate()
    {
        // percentage() already returns the decimal rate (e.g., 0.21 for 21%)
        // so we don't need to divide by 100 again
        return $this->percentage();
    }
}

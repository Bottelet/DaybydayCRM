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
        // Do not cache vatRate or multipleVatRate in constructor; always fetch fresh in methods
    }

    /**
     * Return the Tax Rate as a float
     *
     * @return int
     */
    public function vatRate(): float
    {
        return $this->integerToVatRate();
    }

    public function multipleVatRate(): float
    {
        return 1 + $this->vatRate();
    }

    public function percentage()
    {
        $setting = Setting::select('vat')->first();

        // VAT is stored as percentage * 100 (e.g., 2100 for 21%)
        // Divide by 10000 to get decimal rate (e.g., 0.21)
        return ($setting ? $setting->vat : 2100) / 10000;
    }

    private function integerToVatRate()
    {
        // Always fetch the latest VAT value from the database
        return $this->percentage();
    }
}

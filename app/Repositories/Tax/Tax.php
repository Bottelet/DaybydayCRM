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
    public function vatRate():float
    {
        return $this->vatRate;
    }

    /**
     * @return float
     */
    public function multipleVatRate():float
    {
        return $this->multipleVatRate;
    }

    public function percentage()
    {
        return Setting::select('vat')->first()->vat / 100;
    }

    private function integerToVatRate()
    {
        return $this->percentage() / 100;
    }
}

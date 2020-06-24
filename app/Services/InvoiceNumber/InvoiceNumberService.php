<?php

namespace App\Services\InvoiceNumber;

use App\Models\Setting;

class InvoiceNumberService
{
    private $setting;
    private $lockedSetting;

    public function __construct(InvoiceNumberConfig $config)
    {
        if ($config->isDisabled()) {
            return null;
        }
        $this->setting = Setting::query();
        $this->lockedSetting = $this->setting->lockForUpdate()->first();
    }

    public function setNextInvoiceNumber()
    {
        $currentNumber = $this->nextInvoiceNumber();
        $this->increaseInvoiceNumber();

        return $currentNumber;
    }

    public function setInvoiceNumber(Int $invoiceNumber)
    {
        $this->lockedSetting->invoice_number = $invoiceNumber;
        return $this->lockedSetting->save();
    }

    public function nextInvoiceNumber()
    {
        return $this->setting->first()->invoice_number;
    }

    private function increaseInvoiceNumber()
    {
        $this->lockedSetting->invoice_number++;
        return $this->lockedSetting->save();
    }
}

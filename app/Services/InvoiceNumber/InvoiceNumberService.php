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
        if ($this->lockedSetting) {
            // Set the setting to current + 1 so next call gets the right number
            $this->lockedSetting->invoice_number = $currentNumber + 1;
            $this->lockedSetting->save();
        }

        return $currentNumber;
    }

    public function setInvoiceNumber(int $invoiceNumber)
    {
        if (! $this->lockedSetting) {
            return false;
        }

        $this->lockedSetting->invoice_number = $invoiceNumber;

        return $this->lockedSetting->save();
    }

    public function nextInvoiceNumber()
    {
        $setting = $this->setting->first();
        $settingNumber = $setting ? $setting->invoice_number : 1;
        
        // Also check the maximum invoice number from existing invoices
        // Add 1 to the max to get the next available number
        $maxInvoiceNumber = (\App\Models\Invoice::max('invoice_number') ?? 0) + 1;
        
        // Return the higher of the two to ensure we don't reuse numbers
        return max($settingNumber, $maxInvoiceNumber);
    }

    private function increaseInvoiceNumber()
    {
        $this->lockedSetting->invoice_number++;

        return $this->lockedSetting->save();
    }
}

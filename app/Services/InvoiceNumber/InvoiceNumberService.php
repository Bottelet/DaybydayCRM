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
        $settingNumber = $setting ? $setting->invoice_number : 0;

        $maxInvoice = \App\Models\Invoice::max('invoice_number');
        if ($maxInvoice === null) {
            // No invoices exist, use the setting value as-is
            return $settingNumber;
        }
        // At least one invoice exists, next available is max + 1
        $maxInvoiceNumber = $maxInvoice + 1;

        // Return the higher of the two to ensure we don't reuse numbers
        return max($settingNumber, $maxInvoiceNumber);
    }

    private function increaseInvoiceNumber()
    {
        $this->lockedSetting->invoice_number++;

        return $this->lockedSetting->save();
    }
}

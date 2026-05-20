<?php

namespace App\Services\ClientNumber;

use App\Models\Setting;
use InvalidArgumentException;

class ClientNumberService
{
    private $setting;

    private $lockedSetting;

    public function __construct(ClientNumberConfig $config)
    {
        if ($config->isDisabled()) {
            return;
        }
        $this->setting       = Setting::query();
        $this->lockedSetting = $this->setting->lockForUpdate()->first();
    }

    public function setNextClientNumber()
    {
        $currentNumber = $this->nextClientNumber();
        $this->increaseClientNumber();

        return $currentNumber;
    }

    public function setClientNumber(int $clientNumber)
    {
        if ($clientNumber < 0) {
            throw new InvalidArgumentException('Client number cannot be negative.');
        }

        $this->lockedSetting->client_number = $clientNumber;

        return $this->lockedSetting->save();
    }

    public function nextClientNumber()
    {
        return $this->setting->first()->client_number;
    }

    private function increaseClientNumber()
    {
        $this->lockedSetting->client_number++;

        return $this->lockedSetting->save();
    }
}

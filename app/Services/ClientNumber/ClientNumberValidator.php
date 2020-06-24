<?php

namespace App\Services\ClientNumber;

use App\Models\Client;

class ClientNumberValidator
{
    public function validateClientNumberSize(Int $clientNumber)
    {
        if ($clientNumber <= 9999999 && $clientNumber >= 1) {
            return true;
        }

        return false;
    }

    public function validateClientNumberIsNotLowerThenCurrentMax(Int $clientNumber)
    {
        $currentClientNumber = optional(Client::query()->orderByDesc('client_number')->limit(1)->first())->client_number;
        if ($clientNumber > $currentClientNumber) {
            return true;
        }

        return false;
    }

    public function validateClientNumber(Int $clientNumber)
    {
        if ($this->validateClientNumberIsNotLowerThenCurrentMax($clientNumber) && $this->validateClientNumberSize($clientNumber)) {
            return true;
        }

        return false;
    }
}

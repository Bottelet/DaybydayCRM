<?php

namespace App\Services\ClientNumber;

use App\Models\Client;

class ClientNumberValidator
{
    public function validateClientNumberSize(int $clientNumber)
    {
        if ($clientNumber <= 9999999 && $clientNumber >= 1) {
            return true;
        }

        return false;
    }

    public function validateClientNumberIsNotLowerThenCurrentMax(int $clientNumber)
    {
        $currentClientNumber = optional(Client::query()->orderByDesc('client_number')->limit(1)->first())->client_number;
        if ($clientNumber > $currentClientNumber) {
            return true;
        }

        return false;
    }

    public function validateClientNumber(int $clientNumber)
    {
        if ($this->validateClientNumberIsNotLowerThenCurrentMax($clientNumber) && $this->validateClientNumberSize($clientNumber)) {
            return true;
        }

        return false;
    }
}

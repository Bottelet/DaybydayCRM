<?php

namespace App\Services\ClientNumber;

use App\Models\Client;

class ClientNumberValidator
{
    public function validateClientNumberSize(int $clientNumber)
    {
        return (bool) ($clientNumber <= 9999999 && $clientNumber >= 1);
    }

    public function validateClientNumberIsNotLowerThenCurrentMax(int $clientNumber)
    {
        $currentClientNumber = optional(Client::query()->orderByDesc('client_number')->limit(1)->first())->client_number;

        return (bool) ($clientNumber > $currentClientNumber);
    }

    public function validateClientNumber(int $clientNumber)
    {
        return (bool) ($this->validateClientNumberIsNotLowerThenCurrentMax($clientNumber) && $this->validateClientNumberSize($clientNumber));
    }
}

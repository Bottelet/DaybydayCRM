<?php

namespace App\Services\ClientNumber;

class ClientNumberConfig
{
    private $config;

    public function __construct()
    {
        $this->config = true;
    }

    public function isEnabled(): bool
    {
        return $this->config === true;
    }

    public function isDisabled(): bool
    {
        return $this->config === false;
    }
}

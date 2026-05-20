<?php

namespace App\Services\Setting;

class UpdateOverallSettingsResult
{
    private function __construct(public readonly string $status) {}

    public static function success(): self
    {
        return new self('success');
    }

    public static function clientNumberInvalid(): self
    {
        return new self('client_number_invalid');
    }

    public static function invoiceNumberInvalid(): self
    {
        return new self('invoice_number_invalid');
    }
}

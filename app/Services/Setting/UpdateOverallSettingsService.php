<?php

namespace App\Services\Setting;

use App\Helpers\GetDateFormat;
use App\Models\BusinessHour;
use App\Models\Setting;
use App\Repositories\Currency\Currency;
use App\Services\ClientNumber\ClientNumberValidator;
use App\Services\InvoiceNumber\InvoiceNumberValidator;
use Carbon\Carbon;

class UpdateOverallSettingsService
{
    private const TIME_PARSE_BASE_DATE = '2020-01-01';

    private const DEFAULT_OPEN_TIME = '09:00';

    private const DEFAULT_CLOSE_TIME = '17:00';

    public function __construct(
        private readonly ClientNumberValidator $clientNumberValidator,
        private readonly InvoiceNumberValidator $invoiceNumberValidator,
    ) {}

    public function handle(array $data): UpdateOverallSettingsResult
    {
        $setting = Setting::first();
        if ( ! $setting) {
            $setting = Setting::query()->create([
                'company' => 'Default Company', 'currency' => 'USD', 'country' => 'US', 'language' => 'en',
                'vat'     => 0, 'client_number' => 1, 'invoice_number' => 1, 'max_users' => 10,
            ]);
        }
        if ( ! $this->clientNumberValidator->validateClientNumber((int) $data['client_number'])) {
            return UpdateOverallSettingsResult::clientNumberInvalid();
        }
        if ( ! $this->invoiceNumberValidator->validateInvoiceNumber((int) $data['invoice_number'])) {
            return UpdateOverallSettingsResult::invoiceNumberInvalid();
        }

        $currencyCode = $data['currency'] ?? $setting->currency;

        if ($currencyCode == $setting->currency && ! empty($data['vat'])) {
            $setting->vat = $data['vat'] * 100;
        } elseif ($currencyCode != $setting->currency) {
            $currency          = new Currency($currencyCode);
            $setting->currency = $currencyCode;
            $setting->vat      = empty($data['vat']) ? $currency->getVatPercentage() : $data['vat'] * 100;
        } elseif ( ! empty($data['vat'])) {
            $setting->vat = $data['vat'] * 100;
        }

        $startTimeValue = $data['start_time'] ?? $this->getCurrentOpenTime();
        $endTimeValue   = $data['end_time'] ?? $this->getCurrentCloseTime();

        $startTime = Carbon::parse(self::TIME_PARSE_BASE_DATE . ' ' . $startTimeValue);
        $endTime   = Carbon::parse(self::TIME_PARSE_BASE_DATE . ' ' . $endTimeValue);

        if ($startTime->gt($endTime)) {
            $tmp       = clone $endTime;
            $endTime   = $startTime;
            $startTime = $tmp;
        } elseif ($startTime->eq($endTime)) {
            $endTime->addHour();
        }

        foreach (BusinessHour::all() as $businessHour) {
            $businessHour->update(['open_time' => $startTime->format('H:i:s'), 'close_time' => $endTime->format('H:i:s')]);
        }

        $setting->client_number  = $data['client_number'];
        $setting->invoice_number = $data['invoice_number'];
        if (isset($data['company'])) {
            $setting->company = $data['company'];
        }
        $setting->country  = $data['country'];
        $setting->language = $data['language'];
        $setting->save();
        cache()->delete(GetDateFormat::CACHE_KEY);

        return UpdateOverallSettingsResult::success();
    }

    private function getCurrentOpenTime(): string
    {
        return BusinessHour::query()->orderBy('open_time', 'asc')->value('open_time') ?? self::DEFAULT_OPEN_TIME;
    }

    private function getCurrentCloseTime(): string
    {
        return BusinessHour::query()->orderBy('close_time', 'desc')->value('close_time') ?? self::DEFAULT_CLOSE_TIME;
    }
}

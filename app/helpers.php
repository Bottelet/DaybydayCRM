<?php

use App\Repositories\Format\GetDateFormat;
use App\Repositories\Money\MoneyConverter;
use App\Services\Activity\ActivityLogger;

if (! function_exists('activity')) {
    function activity(?string $logName = null): ActivityLogger
    {
        $defaultLogName = 'default';

        return app(ActivityLogger::class)
            ->withname($logName ?? $defaultLogName);
    }
}

if (! function_exists('frontendDate')) {
    function frontendDate(): string
    {
        return app(GetDateFormat::class)->getFrontendDate();
    }
}
if (! function_exists('frontendTime')) {
    function frontendTime(): string
    {
        return app(GetDateFormat::class)->getFrontendTime();
    }
}
if (! function_exists('carbonTime')) {
    function carbonTime(): string
    {
        return app(GetDateFormat::class)->getCarbonTime();
    }
}

if (! function_exists('carbonFullDateWithText')) {
    function carbonFullDateWithText(): string
    {
        return app(GetDateFormat::class)->getCarbonFullDateWithText();
    }
}

if (! function_exists('carbonDateWithText')) {
    function carbonDateWithText(): string
    {
        return app(GetDateFormat::class)->getCarbonDateWithText();
    }
}

if (! function_exists('carbonDate')) {
    function carbonDate(): string
    {
        return app(GetDateFormat::class)->getCarbonDate();
    }
}

if (! function_exists('isDemo')) {
    function isDemo(): string
    {
        return app()->environment() == 'demo' ? '1' : '0';
    }
}

if (! function_exists('formatMoney')) {
    function formatMoney($amount, $useCode = false): string
    {
        return app(MoneyConverter::class, ['money' => $amount])->format($useCode);
    }
}

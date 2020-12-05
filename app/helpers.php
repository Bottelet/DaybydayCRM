<?php

use App\Services\Activity\ActivityLogger;

if (! function_exists('activity')) {
    function activity(string $logName = null): ActivityLogger
    {
        $defaultLogName = "default";
        return app(ActivityLogger::class)
            ->withname($logName ?? $defaultLogName);
    }
}

if (! function_exists('frontendDate')) {
    function frontendDate(): String
    {
        return app(\App\Repositories\Format\GetDateFormat::class)->getFrontendDate();
    }
}
if (! function_exists('frontendTime')) {
    function frontendTime(): String
    {
        return app(\App\Repositories\Format\GetDateFormat::class)->getFrontendTime();
    }
}
if (! function_exists('carbonTime')) {
    function carbonTime(): String
    {
        return app(\App\Repositories\Format\GetDateFormat::class)->getCarbonTime();
    }
}

if (! function_exists('carbonFullDateWithText')) {
    function carbonFullDateWithText(): String
    {
        return app(\App\Repositories\Format\GetDateFormat::class)->getCarbonFullDateWithText();
    }
}

if (! function_exists('carbonDateWithText')) {
    function carbonDateWithText(): String
    {
        return app(\App\Repositories\Format\GetDateFormat::class)->getCarbonDateWithText();
    }
}

if (! function_exists('carbonDate')) {
    function carbonDate(): String
    {
        return app(\App\Repositories\Format\GetDateFormat::class)->getCarbonDate();
    }
}

if (!function_exists('translations')) {
    function translations()
    {
        try {
            $filename = \Illuminate\Support\Facades\File::get(resource_path() . '/lang/' . app()->getLocale() . '.json');
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            return [];
        }
        $trans = [];

        $entries = json_decode($filename, true);

        foreach ($entries as $k => $v) {
            $trans[$k] = trans($v);
        }
        $trans[$filename] = trans($filename);

        return $trans;
    }
}

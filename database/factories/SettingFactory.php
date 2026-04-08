<?php

/** @var Factory $factory */

use App\Models\Setting;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Setting::class, static function (Faker $faker) {
    return [
        'client_number' => 10000,
        'invoice_number' => 10000,
        'company' => 'test company',
        'max_users' => 10,

    ];
});

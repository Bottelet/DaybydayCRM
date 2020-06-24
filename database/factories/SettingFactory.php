<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Setting;
use Faker\Generator as Faker;

$factory->define(Setting::class, function (Faker $faker) {
    return [
        'client_number' => 10000,
        'invoice_number' => 10000,
        'company' => "test company",
        'max_users' => 10,

    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Invoice;
use Faker\Generator as Faker;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'status' => 'draft',
        'client_id' => factory(\App\Models\Client::class),
    ];
});

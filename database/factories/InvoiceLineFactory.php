<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\InvoiceLine;
use Faker\Generator as Faker;

$factory->define(InvoiceLine::class, function (Faker $faker) {
    return [
        'title' => $faker->name,
        'external_id' => $faker->uuid,
        'type' => $faker->word,
        'quantity' => $faker->randomNumber(1),
        'price' => $faker->randomNumber(4),

    ];
});

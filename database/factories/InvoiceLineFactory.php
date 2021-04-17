<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\InvoiceLine;
use Faker\Generator as Faker;

$factory->define(InvoiceLine::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'external_id' => $faker->uuid,
        'type' => $faker->randomElement(['pieces', 'hours', 'days', 'session', 'kg', 'package']),
        'quantity' => $faker->randomNumber(1),
        'price' => $faker->randomNumber(4),
    ];
});

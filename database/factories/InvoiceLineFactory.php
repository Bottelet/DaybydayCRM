<?php

/** @var Factory $factory */

use App\Models\InvoiceLine;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(InvoiceLine::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'type' => $faker->randomElement(['pieces', 'hours', 'days', 'session', 'kg', 'package']),
        'quantity' => $faker->randomNumber(1),
        'price' => $faker->randomNumber(4),
    ];
});

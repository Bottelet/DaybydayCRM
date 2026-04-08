<?php

/** @var Factory $factory */

use App\Models\Status;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Status::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'color' => '#000',

    ];
});

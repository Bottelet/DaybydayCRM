<?php

/** @var Factory $factory */

use App\Models\Status;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Status::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'title' => $faker->word,
        'color' => '#000',

    ];
});

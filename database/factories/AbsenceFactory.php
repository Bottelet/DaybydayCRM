<?php

/** @var Factory $factory */

use App\Models\Absence;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Absence::class, function (Faker $faker) {
    return [
        'reason' => $faker->word,
        'start_at' => now(),
        'end_at' => now()->addDays(3),
        'user_id' => factory(User::class),
    ];
});

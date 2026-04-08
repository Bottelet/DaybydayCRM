<?php

/** @var Factory $factory */

use App\Models\Absence;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

$factory->define(Absence::class, static function (Faker $faker) {
    return [
        'external_id' => Uuid::uuid4()->toString(),
        'reason' => $faker->word,
        'start_at' => now(),
        'end_at' => now()->addDays(3),
        'user_id' => factory(User::class),
    ];
});

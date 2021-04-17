<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Lead;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Lead::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'external_id' => $faker->uuid,
        'description' => $faker->paragraph,
        'user_created_id' => factory(User::class),
        'user_assigned_id' => factory(User::class),
        'client_id' => factory(\App\Models\Client::class),
        'status_id' => $faker->numberBetween($min = 5, $max = 8),
        'deadline' => $faker->dateTimeThisYear($max = 'now'),
        'created_at' => $faker->dateTimeThisYear($max = 'now'),
        'updated_at' => $faker->dateTimeThisYear($max = 'now'),
    ];
});

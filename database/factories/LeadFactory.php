<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Lead::class, static function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'external_id' => $faker->uuid,
        'description' => $faker->paragraph,
        'user_created_id' => factory(User::class),
        'user_assigned_id' => factory(User::class),
        'client_id' => factory(Client::class),
        'status_id' => $faker->numberBetween($min = 5, $max = 8),
        'deadline' => $faker->dateTimeThisYear($max = 'now'),
        'created_at' => $faker->dateTimeThisYear($max = 'now'),
        'updated_at' => $faker->dateTimeThisYear($max = 'now'),
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Lead;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Lead::class, function (Faker $faker) {
    $user = factory(User::class)->create();
    return [
        'title' => $faker->sentence,
        'external_id' => $faker->uuid,
        'description' => $faker->paragraph,
        'user_created_id' => $user->id,
        'user_assigned_id' => $user->id,
        'qualified' => false,
        'client_id' => factory(\App\Models\Client::class)->create()->id,
        'status_id' => $faker->numberBetween($min = 5, $max = 8),
        'deadline' => $faker->dateTimeThisYear($max = 'now'),
        'created_at' => $faker->dateTimeThisYear($max = 'now'),
        'updated_at' => $faker->dateTimeThisYear($max = 'now'),
    ];
});

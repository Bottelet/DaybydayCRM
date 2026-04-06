<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Project;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
<<<<<<< Updated upstream

$factory->define(Project::class, static function (Faker $faker) {

=======

$factory->define(Project::class, function (Faker $faker) {

>>>>>>> Stashed changes
    return [
        'title' => $faker->sentence,
        'external_id' => $faker->uuid,
        'description' => $faker->paragraph,
        'user_created_id' => factory(User::class),
        'user_assigned_id' => factory(User::class),
        'client_id' => factory(Client::class),
        'status_id' => $faker->numberBetween($min = 1, $max = 4),
        'deadline' => $faker->dateTimeThisYear($max = 'now'),
        'created_at' => $faker->dateTimeThisYear($max = 'now'),
        'updated_at' => $faker->dateTimeThisYear($max = 'now'),
    ];
});

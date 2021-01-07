<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Appointment;
use App\Models\Task;
use App\Models\User;
use Faker\Generator as Faker;

$factory->define(Appointment::class, function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'title' => $faker->word,
        'description' => $faker->text,
        'start_at' => now(),
        'end_at' => now()->addHour(),
        'user_id' => factory(User::class),
        'source_type' => Task::class,
        'source_id' => factory(Task::class),
    ];
});

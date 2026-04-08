<?php

/** @var Factory $factory */

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'source_type' => Task::class,
        'source_id' => factory(Task::class),
        'description' => $faker->paragraph(rand(2, 10)),
    ];
});

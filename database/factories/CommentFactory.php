<?php

/** @var Factory $factory */

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'external_id' => Uuid::uuid4()->toString(),
        'user_id' => factory(User::class),
        'source_type' => Task::class,
        'source_id' => factory(Task::class),
        'description' => $faker->paragraph(rand(2, 10)),
    ];
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */


use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        'external_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'user_id' => factory(User::class),
        'source_type' => Task::class,
        'source_id' => factory(Task::class),
        'description' => $faker->paragraph(rand(2,10))
    ];
});

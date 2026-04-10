<?php

/** @var Factory $factory */

use App\Models\Comment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

class CommentFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Comment::class;

    public function definition()
    {
        return [
            'external_id' => Uuid::uuid4()->toString(),
            'user_id' => User::factory(),
            'source_type' => Task::class,
            'source_id' => Task::factory(),
            'description' => $this->faker->paragraph(rand(2, 10)),
        ];
    }
}

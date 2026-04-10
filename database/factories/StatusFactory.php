<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Status;
use Illuminate\Database\Eloquent\Factory;

class StatusFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Status::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid,
            'title' => $this->faker->word,
            'color' => '#000',
        ];
    }
}

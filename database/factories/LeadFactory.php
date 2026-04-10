<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;

class LeadFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Lead::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'external_id' => $this->faker->uuid,
            'description' => $this->faker->paragraph,
            'user_created_id' => User::factory(),
            'user_assigned_id' => User::factory(),
            'client_id' => Client::factory(),
            'status_id' => $this->faker->numberBetween($min = 5, $max = 8),
            'deadline' => $this->faker->dateTimeThisYear($max = 'now'),
            'created_at' => $this->faker->dateTimeThisYear($max = 'now'),
            'updated_at' => $this->faker->dateTimeThisYear($max = 'now'),
        ];
    }
}

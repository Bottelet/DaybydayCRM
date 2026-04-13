<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\Status;
use App\Models\User;

class ProjectFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'title'            => $this->faker->sentence,
            'external_id'      => $this->faker->uuid,
            'description'      => $this->faker->paragraph,
            'user_created_id'  => User::factory(),
            'user_assigned_id' => User::factory(),
            'client_id'        => Client::factory(),
            'status_id'        => Status::factory(),
            'deadline'         => $this->faker->dateTimeThisYear($max = 'now'),
            'created_at'       => $this->faker->dateTimeThisYear($max = 'now'),
            'updated_at'       => $this->faker->dateTimeThisYear($max = 'now'),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Task;
use App\Models\User;

class AppointmentFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Appointment::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid,
            'title' => $this->faker->word,
            'description' => $this->faker->text,
            'start_at' => now(),
            'end_at' => now()->addHour(),
            'user_id' => User::factory(),
            'source_type' => Task::class,
            'source_id' => Task::factory(),
            'color' => '#000000',
        ];
    }
}

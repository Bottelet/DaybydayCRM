<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

class AbsenceFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Absence::class;

    public function definition()
    {
        return [
            'external_id' => Uuid::uuid4()->toString(),
            'reason' => $this->faker->word,
            'start_at' => now(),
            'end_at' => now()->addDays(3),
            'user_id' => User::factory(),
        ];
    }
}

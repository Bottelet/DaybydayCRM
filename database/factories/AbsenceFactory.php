<?php

namespace Database\Factories;

/* @var Factory $factory */

use App\Enums\AbsenceReason;
use App\Models\Absence;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Ramsey\Uuid\Uuid;

class AbsenceFactory extends Factory
{
    protected $model = Absence::class;

    public function definition()
    {
        $reasons = array_keys(AbsenceReason::values());
        $startAt = $this->faker->dateTimeBetween('-1 month', '+1 month');

        return [
            'external_id' => Uuid::uuid4()->toString(),
            'reason'      => $reasons[array_rand($reasons)],
            'start_at'    => $startAt,
            'end_at'      => $this->faker->dateTimeBetween($startAt, $startAt->format('Y-m-d H:i:s') . ' +1 month'),
            'user_id'     => User::factory(),
        ];
    }
}

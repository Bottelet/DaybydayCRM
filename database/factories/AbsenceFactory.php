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

        return [
            'external_id' => Uuid::uuid4()->toString(),
            'reason'      => $reasons[array_rand($reasons)],
            'start_at'    => now(),
            'end_at'      => now()->addDays(3),
            'user_id'     => User::factory(),
        ];
    }
}

<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Status;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factory;

class StatusFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Status::class;

    public function definition()
    {
        $statusTitles = [
            'Open',
            'In-progress',
            'Pending',
            'Waiting client',
            'Blocked',
            'Closed',
            'Cancelled',
            'Completed',
        ];

        $colors = [
            '#2FA599', // Teal
            '#2FA55E', // Green
            '#EFAC57', // Orange
            '#60C0DC', // Blue
            '#E6733E', // Red-Orange
            '#D75453', // Red
            '#821414', // Dark Red
            '#3CA3BA', // Cyan
        ];

        return [
            'external_id' => $this->faker->uuid,
            'title' => $this->faker->randomElement($statusTitles),
            'source_type' => Task::class,
            'color' => $this->faker->randomElement($colors),
        ];
    }
}

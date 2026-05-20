<?php

namespace Database\Factories;

/* @var Factory $factory */

use App\Models\Department;
use Illuminate\Database\Eloquent\Factory;

class DepartmentFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Department::class;

    public function definition()
    {
        return [
            'name'        => 'factory',
            'external_id' => $this->faker->uuid,
            'description' => 'Mock Department',
        ];
    }
}

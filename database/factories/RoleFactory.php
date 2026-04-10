<?php

/** @var Factory $factory */

use App\Models\Role;
use Illuminate\Database\Eloquent\Factory;

class RoleFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Role::class;

    public function definition()
    {
        return [
            'name' => 'factory',
            'external_id' => $this->faker->uuid,
            'display_name' => 'Factory Role',
            'description' => 'Mock role',

        ];
    }
}

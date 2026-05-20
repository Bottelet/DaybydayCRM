<?php

namespace Database\Factories;

use App\Models\Integration;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntegrationFactory extends Factory
{
    protected $model = Integration::class;

    public function definition(): array
    {
        return [
            'name'          => $this->faker->word(),
            'api_type'      => $this->faker->randomElement(['billing', 'file']),
            'api_key'       => null,
            'client_id'     => null,
            'client_secret' => null,
            'org_id'        => null,
            'user_id'       => null,
        ];
    }

    public function billing(): static
    {
        return $this->state(['api_type' => 'billing']);
    }

    public function file(): static
    {
        return $this->state(['api_type' => 'file']);
    }
}

<?php

namespace Database\Factories;

/* @var Factory $factory */

use App\Models\Client;
use App\Models\Contact;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Database\Eloquent\Factory;

class ClientFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Client::class;

    public function definition()
    {
        return [
            'external_id'  => $this->faker->uuid,
            'vat'          => $this->faker->randomNumber(8),
            'company_name' => $this->faker->company(),
            'address'      => $this->faker->secondaryAddress(),
            'city'         => $this->faker->city(),
            'zipcode'      => $this->faker->postcode(),
            'user_id'      => User::factory(),
            'industry_id'  => Industry::factory(),
            'company_type' => 'ApS',
        ];
    }

    public function configure()
    {
        return $this->afterCreating(static function ($client) {
            Contact::factory()->create(
                [
                    'client_id' => $client->id,
                ]
            );
        });
    }

    public function withIndustry(?Industry $industry = null)
    {
        return $this->state(function () use ($industry) {
            return [
                'industry_id' => ($industry->id ?? Industry::factory()),
            ];
        });
    }
}

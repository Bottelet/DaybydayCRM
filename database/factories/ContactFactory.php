<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factory;

class ContactFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Contact::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'external_id' => $this->faker->uuid,
            'email' => $this->faker->email,
            'primary_number' => $this->faker->randomNumber(8),
            'secondary_number' => $this->faker->randomNumber(8),
            'client_id' => Client::factory(),
            'is_primary' => 1,
        ];
    }
}

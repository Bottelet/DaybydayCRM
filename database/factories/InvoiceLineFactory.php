<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factory;

class InvoiceLineFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = InvoiceLine::class;

    public function definition()
    {
        return [
            'title' => $this->faker->word,
            'external_id' => $this->faker->uuid,
            'type' => $this->faker->randomElement(['pieces', 'hours', 'days', 'session', 'kg', 'package']),
            'quantity' => $this->faker->randomNumber(1),
            'price' => $this->faker->randomNumber(4),
        ];
    }
}

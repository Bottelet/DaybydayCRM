<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factory;

class InvoiceFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid,
            'status' => 'draft',
            'client_id' => Client::factory(),
        ];
    }
}

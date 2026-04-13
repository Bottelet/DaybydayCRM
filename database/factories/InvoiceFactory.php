<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;

class InvoiceFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid,
            'status'      => InvoiceStatus::draft(),
            'client_id'   => Client::factory(),
        ];
    }
}

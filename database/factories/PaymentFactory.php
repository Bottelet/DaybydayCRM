<?php

namespace Database\Factories;

/* @var Factory $factory */

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factory;

class PaymentFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'external_id'    => $this->faker->uuid,
            'invoice_id'     => Invoice::factory(),
            'amount'         => 1000,
            'payment_date'   => today(),
            'payment_source' => 'bank',
        ];
    }
}

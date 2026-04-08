<?php

/** @var Factory $factory */

use App\Models\Invoice;
use App\Models\Payment;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Payment::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'invoice_id' => factory(Invoice::class),
        'amount' => 1000,
        'payment_date' => today(),
        'payment_source' => 'bank',
    ];
});

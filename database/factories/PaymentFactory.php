<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Payment;
use Faker\Generator as Faker;

$factory->define(Payment::class, function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'invoice_id' => factory(\App\Models\Invoice::class),
        'amount' => 1000,
        'payment_date' => today(),
        'payment_source' => 'bank'
    ];
});

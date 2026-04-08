<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Invoice;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Invoice::class, function (Faker $faker) {
    return [
        'status' => 'draft',
        'client_id' => factory(Client::class),
    ];
});

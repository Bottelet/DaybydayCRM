<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Invoice;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Invoice::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'status' => 'draft',
        'client_id' => factory(Client::class),
    ];
});

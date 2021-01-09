<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Client;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'vat' => $faker->randomNumber(8),
        'company_name' => $faker->company(),
        'address' => $faker->secondaryAddress(),
        'city' => $faker->city(),
        'zipcode' => $faker->postcode(),
        'industry_id' => $faker->numberBetween($min = 1, $max = 25),
        'user_id' => factory(App\Models\User::class),
        'company_type' => 'ApS',
    ];
});
$factory->afterCreating(Client::class, function ($client, $faker) {
    factory(\App\Models\Contact::class)->create(
        [
            'client_id' => $client->id
        ]
    );
});

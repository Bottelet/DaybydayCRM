<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Client::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'vat' => $faker->randomNumber(8),
        'company_name' => $faker->company(),
        'address' => $faker->secondaryAddress(),
        'city' => $faker->city(),
        'zipcode' => $faker->postcode(),
        'industry_id' => $faker->numberBetween($min = 1, $max = 25),
        'user_id' => factory(User::class),
        'company_type' => 'ApS',
    ];
});
<<<<<<< Updated upstream
$factory->afterCreating(Client::class, static function ($client, $faker) {
=======
$factory->afterCreating(Client::class, function ($client, $faker) {
>>>>>>> Stashed changes
    factory(Contact::class)->create(
        [
            'client_id' => $client->id,
        ]
    );
});

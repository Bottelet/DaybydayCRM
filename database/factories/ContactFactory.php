<?php

/** @var Factory $factory */

use App\Models\Contact;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Contact::class, static function (Faker $faker) {
    return [
        'name' => $faker->name,
        'external_id' => $faker->uuid,
        'email' => $faker->email,
        'primary_number' => $faker->randomNumber(8),
        'secondary_number' => $faker->randomNumber(8),
        'client_id' => 1,
        'is_primary' => 1,
    ];
});

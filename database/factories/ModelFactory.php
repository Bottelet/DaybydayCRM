<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\Models\User::class, function (Faker\Generator $faker) {
    return [
        'name'            => $faker->name,
        'email'           => $faker->email,
        'password'        => bcrypt(str_random(10)),
        'address'         => $faker->address(),
        'work_number'     => $faker->randomNumber(3).$faker->randomNumber(7),
        'personal_number' => $faker->randomNumber(3).$faker->randomNumber(7),
        'remember_token'  => str_random(10),
    ];
});

$factory->define(App\Models\Client::class, function (Faker\Generator $faker) {
    return [
        'name'                          => $faker->company(),
        'primary_email'                 => $faker->email(),
        'vat'                           => $faker->randomNumber(8),
        'billing_address1'              => $faker->streetAddress(),
        'billing_address2'              => $faker->secondaryAddress(),
        'billing_city'                  => $faker->city(),
        'billing_state'                 => $faker->state(),
        'billing_zipcode'               => $faker->postcode(),
        'billing_country'               => $faker->country(),
        'shipping_address1'             => $faker->streetAddress(),
        'shipping_address2'             => $faker->secondaryAddress(),
        'shipping_city'                 => $faker->city(),
        'shipping_state'                => $faker->state(),
        'shipping_zipcode'              => $faker->postcode(),
        'shipping_country'              => $faker->country(),
        'primary_number'                => $faker->randomNumber(3).$faker->randomNumber(7),
        'secondary_number'              => $faker->randomNumber(3).$faker->randomNumber(7),
        'industry_id'                   => $faker->numberBetween($min = 1, $max = 25),
        'user_id'                       => $faker->numberBetween($min = 1, $max = 6),
        'company_type'                  => 'ApS',
    ];
});

$factory->define(App\Models\Contact::class, function (Faker\Generator $faker) {
    return [
        'name'                            => $faker->name(),
        'job_title'                       => $faker->jobTitle(),
        'email'                           => $faker->email(),
        'address1'                        => $faker->streetAddress(),
        'address2'                        => $faker->secondaryAddress(),
        'city'                            => $faker->city(),
        'state'                           => $faker->state(),
        'zipcode'                         => $faker->postcode(),
        'country'                         => $faker->country(),
        'primary_number'                  => $faker->randomNumber(3).$faker->randomNumber(7),
        'secondary_number'                => $faker->randomNumber(3).$faker->randomNumber(7),
        'client_id'                       => $faker->numberBetween($min = 1, $max = 50),
    ];
});

$factory->define(App\Models\Task::class, function (Faker\Generator $faker) {
    return [
        'title'            => $faker->sentence,
        'description'      => $faker->paragraph,
        'user_created_id'  => $faker->numberBetween($min = 1, $max = 3),
        'user_assigned_id' => $faker->numberBetween($min = 1, $max = 3),
        'client_id'        => $faker->numberBetween($min = 1, $max = 50),
        'status'           => $faker->numberBetween($min = 1, $max = 2),
        'deadline'         => $faker->dateTimeThisYear($max = 'now'),
        'created_at'       => $faker->dateTimeThisYear($max = 'now'),
        'updated_at'       => $faker->dateTimeThisYear($max = 'now'),
    ];
});

$factory->define(App\Models\Lead::class, function (Faker\Generator $faker) {
    return [
        'title'            => $faker->sentence,
        'description'      => $faker->paragraph,
        'user_created_id'  => $faker->numberBetween($min = 1, $max = 3),
        'user_assigned_id' => $faker->numberBetween($min = 1, $max = 3),
        'client_id'        => $faker->numberBetween($min = 1, $max = 50),
        'status'           => $faker->numberBetween($min = 1, $max = 2),
        'contact_date'     => $faker->dateTimeThisYear($max = 'now'),
        'created_at'       => $faker->dateTimeThisYear($max = 'now'),
        'updated_at'       => $faker->dateTimeThisYear($max = 'now'),
    ];
});

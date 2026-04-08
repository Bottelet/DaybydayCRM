<?php

/** @var Factory $factory */

use App\Models\Department;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => bcrypt('secretpassword'),
        'address' => $faker->secondaryAddress(),
        'primary_number' => $faker->randomNumber(8),
        'secondary_number' => $faker->randomNumber(8),
        'remember_token' => null,
        'language' => 'en',
    ];
});

$factory->afterCreating(User::class, function ($user, $faker) {
    $user->department()->attach(Department::first()->id);
});

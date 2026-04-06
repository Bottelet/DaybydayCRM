<?php

/** @var Factory $factory */

use App\Models\Department;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(User::class, static function (Faker $faker) {
    return [
        'name' => $faker->name,
        'external_id' => $faker->uuid,
        'email' => $faker->email,
        'password' => bcrypt('secretpassword'),
        'address' => $faker->secondaryAddress(),
        'primary_number' => $faker->randomNumber(8),
        'secondary_number' => $faker->randomNumber(8),
        'remember_token' => null,
        'language' => 'en',
    ];
});

<<<<<<< Updated upstream
$factory->afterCreating(User::class, static function ($user, $faker) {
=======
$factory->afterCreating(User::class, function ($user, $faker) {
>>>>>>> Stashed changes
    $user->department()->attach(Department::first()->id);
});

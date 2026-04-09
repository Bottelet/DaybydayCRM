<?php

/** @var Factory $factory */

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Str;

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

$factory->afterCreating(User::class, static function ($user, $faker) {
    // Ensure at least one department exists
    if (Department::count() === 0) {
        factory(Department::class)->create();
    }
    $user->department()->attach(Department::first()->id);

    // Ensure default employee role exists and attach it to user
    $defaultRole = Role::firstOrCreate(
        ['name' => 'employee'],
        [
            'display_name' => 'Employee',
            'description' => 'Default employee role',
            'external_id' => Str::uuid()->toString(),
        ]
    );
    $user->attachRole($defaultRole);
});

<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Role;
use Faker\Generator as Faker;

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => 'factory',
        'external_id' => $faker->uuid,
        'display_name' => 'Factory Role',
        'description' => 'Mock role',

    ];
});

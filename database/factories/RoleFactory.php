<?php

/** @var Factory $factory */

use App\Models\Role;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Role::class, function (Faker $faker) {
    return [
        'name' => 'factory',
        'display_name' => 'Factory Role',
        'description' => 'Mock role',

    ];
});

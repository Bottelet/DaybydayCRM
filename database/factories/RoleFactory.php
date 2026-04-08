<?php

/** @var Factory $factory */

use App\Models\Role;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Role::class, static function (Faker $faker) {
    return [
        'name' => 'factory',
        'external_id' => $faker->uuid,
        'display_name' => 'Factory Role',
        'description' => 'Mock role',

    ];
});

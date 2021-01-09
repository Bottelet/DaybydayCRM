<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Department;
use Faker\Generator as Faker;

$factory->define(Department::class, function (Faker $faker) {
    return [
        'name' => 'factory',
        'external_id' => $faker->uuid,
        'description' => 'Mock Department',
    ];
});

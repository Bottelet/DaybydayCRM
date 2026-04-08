<?php

/** @var Factory $factory */

use App\Models\Department;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Department::class, function (Faker $faker) {
    return [
        'name' => 'factory',
        'description' => 'Mock Department',
    ];
});

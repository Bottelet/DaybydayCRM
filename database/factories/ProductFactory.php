<?php

/** @var Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->text(),
        'number' => Uuid::uuid1()->toString(),
        'price' => $faker->numberBetween(1000, 10000),
        'default_type' => 'hours',
        'archived' => false,
    ];
});

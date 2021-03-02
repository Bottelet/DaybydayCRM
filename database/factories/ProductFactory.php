<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Product;
use Faker\Generator as Faker;
use Ramsey\Uuid\Uuid;

$factory->define(Product::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'external_id' => $faker->uuid,
        'description' => $faker->text(),
        'number' => Uuid::uuid1()->toString(),
        'price' => $faker->numberBetween(1000,10000),
        'default_type' => 'hours',
        'archived' => false,
    ];
});

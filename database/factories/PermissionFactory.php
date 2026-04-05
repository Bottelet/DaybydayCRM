<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Permission;
use Faker\Factory as FakerFactory;

$factory->define(Permission::class, function () {
    $faker = FakerFactory::create();

    return [
        'external_id' => $faker->uuid,
        'display_name' => $faker->words(2, true),
        'name' => $faker->unique()->slug,
        'description' => $faker->sentence,
        'grouping' => $faker->word,
    ];
});

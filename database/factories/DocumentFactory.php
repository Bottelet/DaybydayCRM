<?php

/** @var Factory $factory */

use App\Models\Client;
use App\Models\Document;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Document::class, function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'size' => $faker->randomFloat(2, 0.1, 100),
        'path' => '/storage/documents/'.$faker->uuid.'.pdf',
        'original_filename' => $faker->word.'.pdf',
        'mime' => 'application/pdf',
        'integration_type' => 'local',
        'source_type' => Client::class,
        'source_id' => factory(Client::class),
    ];
});

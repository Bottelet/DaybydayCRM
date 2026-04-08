<?php

use App\Models\Document;
use Faker\Generator as Faker;

$factory->define(Document::class, function (Faker $faker) {
    return [
        'external_id' => $faker->uuid,
        'name' => $faker->word,
        'size' => $faker->randomFloat(2, 0.1, 15),
        'path' => $faker->filePath(),
        'original_filename' => $faker->word . '.pdf',
        'mime' => 'application/pdf',
        'integration_id' => null,
        'integration_type' => null,
    ];
});

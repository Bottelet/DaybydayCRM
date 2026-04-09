<?php

/** @var Factory $factory */

use App\Models\Industry;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;

$factory->define(Industry::class, static function (Faker $faker) {
    return [
        'external_id' => $faker->uuid(),
        'name' => $faker->randomElement([
            'Accommodations',
            'Accounting',
            'Auto',
            'Beauty & Cosmetics',
            'Carpenter',
            'Communications',
            'Computer & IT',
            'Construction',
            'Consulting',
            'Education',
            'Electronics',
            'Entertainment',
            'Food & Beverages',
            'Legal Services',
            'Marketing',
            'Real Estate',
            'Retail',
            'Sports',
            'Technology',
            'Tourism',
            'Transportation',
            'Travel',
            'Utilities',
            'Web Services',
            'Other',
        ]),
    ];
});

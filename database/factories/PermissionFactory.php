<?php

/** @var Factory $factory */

use App\Models\Permission;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Eloquent\Factory;

class PermissionFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Permission::class;

    public function definition()
    {
        $faker = FakerFactory::create();

        return [
            'external_id' => $faker->uuid,
            'display_name' => $faker->words(2, true),
            'name' => $faker->unique()->slug,
            'description' => $faker->sentence,
            'grouping' => $faker->word,
        ];
    }
}

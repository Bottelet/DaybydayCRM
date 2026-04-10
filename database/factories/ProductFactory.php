<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Product;
use Illuminate\Database\Eloquent\Factory;
use Ramsey\Uuid\Uuid;

class ProductFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'external_id' => $this->faker->uuid,
            'description' => $this->faker->text(),
            'number' => Uuid::uuid1()->toString(),
            'price' => $this->faker->numberBetween(1000, 10000),
            'default_type' => 'hours',
            'archived' => false,
        ];
    }
}

<?php

namespace Database\Factories;

/** @var Factory $factory */

use App\Models\Industry;
use Illuminate\Database\Eloquent\Factory;

class IndustryFactory extends \Illuminate\Database\Eloquent\Factories\Factory
{
    protected $model = Industry::class;

    public function definition()
    {
        return [
            'external_id' => $this->faker->uuid(),
            'name' => $this->faker->randomElement([
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
    }
}

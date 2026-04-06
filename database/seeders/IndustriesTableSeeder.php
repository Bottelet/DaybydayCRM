<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ramsey\Uuid\Uuid;

class IndustriesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('industries')->delete();

        DB::table('industries')->insert([
            0 => [
                'id' => 1,
                'name' => 'Accommodations',
                'external_id' => Uuid::uuid4(),
            ],
            1 => [
                'id' => 2,
                'name' => 'Accounting',
                'external_id' => Uuid::uuid4(),
            ],
            2 => [
                'id' => 3,
                'name' => 'Auto',
                'external_id' => Uuid::uuid4(),
            ],
            3 => [
                'id' => 4,
                'name' => 'Beauty & Cosmetics',
                'external_id' => Uuid::uuid4(),
            ],
            4 => [
                'id' => 5,
                'name' => 'Carpenter',
                'external_id' => Uuid::uuid4(),
            ],
            5 => [
                'id' => 6,
                'name' => 'Communications',
                'external_id' => Uuid::uuid4(),
            ],
            6 => [
                'id' => 7,
                'name' => 'Computer & IT',
                'external_id' => Uuid::uuid4(),
            ],
            7 => [
                'id' => 8,
                'name' => 'Construction',
                'external_id' => Uuid::uuid4(),
            ],
            8 => [
                'id' => 9,
                'name' => 'Consulting',
                'external_id' => Uuid::uuid4(),
            ],
            9 => [
                'id' => 10,
                'name' => 'Education',
                'external_id' => Uuid::uuid4(),
            ],
            10 => [
                'id' => 11,
                'name' => 'Electronics',
                'external_id' => Uuid::uuid4(),
            ],
            11 => [
                'id' => 12,
                'name' => 'Entertainment',
                'external_id' => Uuid::uuid4(),
            ],
            12 => [
                'id' => 13,
                'name' => 'Food & Beverages',
                'external_id' => Uuid::uuid4(),
            ],
            13 => [
                'id' => 14,
                'name' => 'Legal Services',
                'external_id' => Uuid::uuid4(),
            ],
            14 => [
                'id' => 15,
                'name' => 'Marketing',
                'external_id' => Uuid::uuid4(),
            ],
            15 => [
                'id' => 16,
                'name' => 'Real Estate',
                'external_id' => Uuid::uuid4(),
            ],
            16 => [
                'id' => 17,
                'name' => 'Retail',
                'external_id' => Uuid::uuid4(),
            ],
            17 => [
                'id' => 18,
                'name' => 'Sports',
                'external_id' => Uuid::uuid4(),
            ],
            18 => [
                'id' => 19,
                'name' => 'Technology',
                'external_id' => Uuid::uuid4(),
            ],
            19 => [
                'id' => 20,
                'name' => 'Tourism',
                'external_id' => Uuid::uuid4(),
            ],
            20 => [
                'id' => 21,
                'name' => 'Transportation',
                'external_id' => Uuid::uuid4(),
            ],
            21 => [
                'id' => 22,
                'name' => 'Travel',
                'external_id' => Uuid::uuid4(),
            ],
            22 => [
                'id' => 23,
                'name' => 'Utilities',
                'external_id' => Uuid::uuid4(),
            ],
            23 => [
                'id' => 24,
                'name' => 'Web Services',
                'external_id' => Uuid::uuid4(),
            ],
            24 => [
                'id' => 25,
                'name' => 'Other',
                'external_id' => Uuid::uuid4(),
            ],
        ]);
    }
}

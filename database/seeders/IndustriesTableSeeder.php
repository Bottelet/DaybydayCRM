<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
            [
                'name' => 'Accommodations',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Accounting',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Auto',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Beauty & Cosmetics',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Carpenter',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Communications',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Computer & IT',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Construction',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Consulting',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Education',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Electronics',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Entertainment',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Food & Beverages',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Legal Services',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Marketing',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Real Estate',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Retail',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Sports',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Technology',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Tourism',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Transportation',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Travel',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Utilities',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Web Services',
                'external_id' => Uuid::uuid4(),
            ],
            [
                'name' => 'Other',
                'external_id' => Uuid::uuid4(),
            ],
        ]);
    }
}

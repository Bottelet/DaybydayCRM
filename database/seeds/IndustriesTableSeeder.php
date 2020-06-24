<?php

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
        \DB::table('industries')->delete();
        
        \DB::table('industries')->insert(array(
            0 =>
            array(
                'id' => 1,
                'name' => 'Accommodations',
                'external_id' => Uuid::uuid4()
            ),
            1 =>
            array(
                'id' => 2,
                'name' => 'Accounting',
                'external_id' => Uuid::uuid4()
            ),
            2 =>
            array(
                'id' => 3,
                'name' => 'Auto',
                'external_id' => Uuid::uuid4()
            ),
            3 =>
            array(
                'id' => 4,
                'name' => 'Beauty & Cosmetics',
                'external_id' => Uuid::uuid4()
            ),
            4 =>
            array(
                'id' => 5,
                'name' => 'Carpenter',
                'external_id' => Uuid::uuid4()
            ),
            5 =>
            array(
                'id' => 6,
                'name' => 'Communications',
                'external_id' => Uuid::uuid4()
            ),
            6 =>
            array(
                'id' => 7,
                'name' => 'Computer & IT',
                'external_id' => Uuid::uuid4()
            ),
            7 =>
            array(
                'id' => 8,
                'name' => 'Construction',
                'external_id' => Uuid::uuid4()
            ),
            8 =>
            array(
                'id' => 9,
                'name' => 'Consulting',
                'external_id' => Uuid::uuid4()
            ),
            9 =>
            array(
                'id' => 10,
                'name' => 'Education',
                'external_id' => Uuid::uuid4()
            ),
            10 =>
            array(
                'id' => 11,
                'name' => 'Electronics',
                'external_id' => Uuid::uuid4()
            ),
            11 =>
            array(
                'id' => 12,
                'name' => 'Entertainment',
                'external_id' => Uuid::uuid4()
            ),
            12 =>
            array(
                'id' => 13,
                'name' => 'Food & Beverages',
                'external_id' => Uuid::uuid4()
            ),
            13 =>
            array(
                'id' => 14,
                'name' => 'Legal Services',
                'external_id' => Uuid::uuid4()
            ),
            14 =>
            array(
                'id' => 15,
                'name' => 'Marketing',
                'external_id' => Uuid::uuid4()
            ),
            15 =>
            array(
                'id' => 16,
                'name' => 'Real Estate',
                'external_id' => Uuid::uuid4()
            ),
            16 =>
            array(
                'id' => 17,
                'name' => 'Retail',
                'external_id' => Uuid::uuid4()
            ),
            17 =>
            array(
                'id' => 18,
                'name' => 'Sports',
                'external_id' => Uuid::uuid4()
            ),
            18 =>
            array(
                'id' => 19,
                'name' => 'Technology',
                'external_id' => Uuid::uuid4()
            ),
            19 =>
            array(
                'id' => 20,
                'name' => 'Tourism',
                'external_id' => Uuid::uuid4()
            ),
            20 =>
            array(
                'id' => 21,
                'name' => 'Transportation',
                'external_id' => Uuid::uuid4()
            ),
            21 =>
            array(
                'id' => 22,
                'name' => 'Travel',
                'external_id' => Uuid::uuid4()
            ),
            22 =>
            array(
                'id' => 23,
                'name' => 'Utilities',
                'external_id' => Uuid::uuid4()
            ),
            23 =>
            array(
                'id' => 24,
                'name' => 'Web Services',
                'external_id' => Uuid::uuid4()
            ),
            24 =>
            array(
                'id' => 25,
                'name' => 'Other',
                'external_id' => Uuid::uuid4()
            ),
        ));
    }
}

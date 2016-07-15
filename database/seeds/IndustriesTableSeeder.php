<?php

use Illuminate\Database\Seeder;

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
        
        \DB::table('industries')->insert(array (
            0 =>
            array (
                'id' => 1,
                'name' => 'Accommodations',
            ),
            1 =>
            array (
                'id' => 2,
                'name' => 'Accounting',
            ),
            2 =>
            array (
                'id' => 3,
                'name' => 'Auto',
            ),
            3 =>
            array (
                'id' => 4,
                'name' => 'Beauty & Cosmetics',
            ),
            4 =>
            array (
                'id' => 5,
                'name' => 'Carpenter',
            ),
            5 =>
            array (
                'id' => 6,
                'name' => 'Communications',
            ),
            6 =>
            array (
                'id' => 7,
                'name' => 'Computer & IT',
            ),
            7 =>
            array (
                'id' => 8,
                'name' => 'Construction',
            ),
            8 =>
            array (
                'id' => 9,
                'name' => 'Consulting',
            ),
            9 =>
            array (
                'id' => 10,
                'name' => 'Education',
            ),
            10 =>
            array (
                'id' => 11,
                'name' => 'Electronics',
            ),
            11 =>
            array (
                'id' => 12,
                'name' => 'Entertainment',
            ),
            12 =>
            array (
                'id' => 13,
                'name' => 'Food & Beverages',
            ),
            13 =>
            array (
                'id' => 14,
                'name' => 'Legal Services',
            ),
            14 =>
            array (
                'id' => 15,
                'name' => 'Marketing',
            ),
            15 =>
            array (
                'id' => 16,
                'name' => 'Real Estate',
            ),
            16 =>
            array (
                'id' => 17,
                'name' => 'Retail',
            ),
            17 =>
            array (
                'id' => 18,
                'name' => 'Sports',
            ),
            18 =>
            array (
                'id' => 19,
                'name' => 'Technology',
            ),
            19 =>
            array (
                'id' => 20,
                'name' => 'Tourism',
            ),
            20 =>
            array (
                'id' => 21,
                'name' => 'Transportation',
            ),
            21 =>
            array (
                'id' => 22,
                'name' => 'Travel',
            ),
            22 =>
            array (
                'id' => 23,
                'name' => 'Utilities',
            ),
            23 =>
            array (
                'id' => 24,
                'name' => 'Web Services',
            ),
            24 =>
            array (
                'id' => 25,
                'name' => 'Other',
            ),
        ));
    }
}

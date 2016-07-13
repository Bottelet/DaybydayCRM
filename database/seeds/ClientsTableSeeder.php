<?php

use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('clients')->delete();
        
        \DB::table('clients')->insert(array (
            0 => 
            array (
                'id' => 9,
                'name' => 'Inge Sunke Jakobsen',
                'email' => 'asdasd@asd.com',
                'primary_number' => 0,
                'secondary_number' => 0,
                'address' => 'Hovedvagtsgade 6, st. tv.',
                'zipcode' => 1103,
                'city' => 'København K',
                'company_name' => 'Contrast ApS',
                'vat' => 36960043,
                'industry' => '',
                'company_type' => 'Anpartsselskab',
                'fk_user_id' => 1,
                'industry_id' => 17,
                'created_at' => '2016-06-04 13:50:23',
                'updated_at' => '2016-06-04 13:50:23',
            ),
            1 => 
            array (
                'id' => 10,
                'name' => 'Jan Hansen',
                'email' => 'jb@yellowline.dk',
                'primary_number' => 0,
                'secondary_number' => 0,
                'address' => 'Ørnekærs Vænge 188',
                'zipcode' => 2635,
                'city' => 'Ishøj',
                'company_name' => 'Yellow Line I/S',
                'vat' => 29004692,
                'industry' => '',
                'company_type' => 'Interessentskab',
                'fk_user_id' => 1,
                'industry_id' => 23,
                'created_at' => '2016-06-04 13:51:24',
                'updated_at' => '2016-06-04 13:51:24',
            ),
        ));
        
        
    }
}

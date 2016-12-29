<?php

use Illuminate\Database\Seeder;

class LeadsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        
        
        \DB::table('leads')->insert(array (
            0 =>
            array (
                'id' => 1,
                'title' => 'Sell Item',
                'note' => 'Try and sell this new Item',
                'status' => 1,
                'userassign_id' => 1,
                'client_id' => 9,
                'usercreated_id' => 1,
                'contact_date' => '2016-06-18 12:00:00',
                'created_at' => '2016-06-04 13:51:10',
                'updated_at' => '2016-06-04 13:51:10',
            ),
            1 =>
            array (
                'id' => 2,
                'title' => 'Contact Client about new offer',
                'note' => 'Give them a call about the new items',
                'status' => 1,
                'userassign_id' => 1,
                'client_id' => 10,
                'usercreated_id' => 1,
                'contact_date' => '2016-06-18 13:00:00',
                'created_at' => '2016-06-04 13:56:27',
                'updated_at' => '2016-06-04 13:56:27',
            ),
            2 =>
            array (
                'id' => 3,
                'title' => 'Client wants to know more about item',
                'note' => 'Give the client a call, about the item',
                'status' => 2,
                'userassign_id' => 1,
                'client_id' => 10,
                'usercreated_id' => 1,
                'contact_date' => '2016-06-14 12:00:00',
                'created_at' => '2016-06-04 13:57:07',
                'updated_at' => '2016-06-04 13:57:07',
            ),
        ));
    }
}

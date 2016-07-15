<?php

use Illuminate\Database\Seeder;

class TasksTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('tasks')->delete();
        
        \DB::table('tasks')->insert(array (
            0 =>
            array (
                'id' => 1,
                'title' => 'Create this item for customer',
                'description' => 'Lets do something smart',
                'status' => 1,
                'fk_user_id_assign' => 1,
                'fk_user_id_created' => 1,
                'fk_client_id' => 10,
                'deadline' => '2016-06-07',
                'created_at' => '2016-06-04 13:51:52',
                'updated_at' => '2016-06-04 13:51:52',
            ),
            1 =>
            array (
                'id' => 2,
                'title' => 'Let\'s help the client',
                'description' => 'Give client a call ASAP',
                'status' => 1,
                'fk_user_id_assign' => 1,
                'fk_user_id_created' => 1,
                'fk_client_id' => 9,
                'deadline' => '2016-06-07',
                'created_at' => '2016-06-04 13:51:56',
                'updated_at' => '2016-06-04 13:51:56',
            ),
            2 =>
            array (
                'id' => 3,
                'title' => 'Send offer',
                'description' => 'Send offer to client',
                'status' => 2,
                'fk_user_id_assign' => 1,
                'fk_user_id_created' => 1,
                'fk_client_id' => 10,
                'deadline' => '2016-06-07',
                'created_at' => '2016-06-04 13:52:42',
                'updated_at' => '2016-06-04 13:52:42',
            ),
        ));
    }
}

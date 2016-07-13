<?php

use Illuminate\Database\Seeder;

class NotificationCategoriesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('notification_categories')->delete();
        
        \DB::table('notification_categories')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'task.assign',
                'text' => '{from.name} assigned a task to you',
            ),
        ));
        
        
    }
}

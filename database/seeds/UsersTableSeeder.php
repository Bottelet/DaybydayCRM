<?php

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('users')->delete();
        
        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => '$2y$10$e3w9Ztdfpbuqw5qJDOqu6OrccxqYfOD3g6BefMLiDVaoEXv2ybsNC',
                'address' => '',
                'work_number' => 0,
                'personal_number' => 0,
                'image_path' => '',
                'remember_token' => NULL,
                'created_at' => '2016-06-04 13:42:19',
                'updated_at' => '2016-06-04 13:42:19',
            ),
        ));
        
        
    }
}

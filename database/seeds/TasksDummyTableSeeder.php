<?php

use Illuminate\Database\Seeder;

class TasksDummyTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
     factory(App\Tasks::class, 175)->create()->each(function($c){
           
          });
    }
}

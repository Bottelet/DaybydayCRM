<?php

use Illuminate\Database\Seeder;

class ClientsDummyTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
         factory(App\Client::class, 50)->create()->each(function($c){
           
          });
    }
}

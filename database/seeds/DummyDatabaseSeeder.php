<?php

use Illuminate\Database\Seeder;

class DummyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $this->call(UsersDummyTableSeeder::class);
        sleep(1);
        $this->call(ClientsDummyTableSeeder::class);
        sleep(1);
        $this->call(TasksDummyTableSeeder::class);
        sleep(1);
        $this->call(LeadsDummyTableSeeder::class);
    }

}

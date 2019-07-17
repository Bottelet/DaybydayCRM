<?php

use Illuminate\Database\Seeder;

class DummyDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->call('UsersDummyTableSeeder');
        $this->call('ClientsDummyTableSeeder');
        $this->call('ContactsDummyTableSeeder');
        $this->call('TasksDummyTableSeeder');
        $this->call('LeadsDummyTableSeeder');
    }
}

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
        $this->call('UsersDummyTableSeeder');
        $this->call('ClientsDummyTableSeeder');
        $this->call('TasksDummyTableSeeder');
        $this->call('LeadsDummyTableSeeder');
    }
}

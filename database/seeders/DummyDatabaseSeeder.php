<?php

namespace Database\Seeders;

use Database\Seeders\Dummy\ClientsDummyTableSeeder;
use Database\Seeders\Dummy\LeadsDummyTableSeeder;
use Database\Seeders\Dummy\ProjectsDummyTableSeeder;
use Database\Seeders\Dummy\TasksDummyTableSeeder;
use Database\Seeders\Dummy\UsersDummyTableSeeder;
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
        $this->call(ClientsDummyTableSeeder::class);
        $this->call(TasksDummyTableSeeder::class);
        $this->call(LeadsDummyTableSeeder::class);
        $this->call(ProjectsDummyTableSeeder::class);
        $this->call(OfferSeeder::class);
        $this->call(ProductSeeder::class);
    }
}

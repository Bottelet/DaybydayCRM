<?php

use Illuminate\Database\Seeder;

class LeadsDummyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Models\Lead::class, 10)->create()->each(function ($c) {
        });
    }
}

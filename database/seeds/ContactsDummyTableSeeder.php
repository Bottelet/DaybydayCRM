<?php

use Illuminate\Database\Seeder;

class ContactsDummyTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     */
    public function run()
    {
        factory(App\Models\Contact::class, 50)->create()->each(function ($c) {
        });
    }
}

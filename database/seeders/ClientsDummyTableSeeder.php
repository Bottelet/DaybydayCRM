<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contact;
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
        Client::factory()->count(50)->create()->each(function ($c) {
            Contact::factory()->create([
                'client_id' => $c->id,
            ]);
        });
    }
}

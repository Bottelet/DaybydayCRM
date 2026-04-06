<?php

<<<<<<< Updated upstream:database/seeders/ClientsDummyTableSeeder.php
namespace Database\Seeders;

=======
>>>>>>> Stashed changes:database/seeds/ClientsDummyTableSeeder.php
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
        factory(Client::class, 50)->create()->each(function ($c) {
            factory(Contact::class)->create([
                'client_id' => $c->id,
            ]);
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Industry;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsTableSeeder extends Seeder
{
    private const SEED_CLIENT_NAME = 'Playwright Seed Client';

    private const SEED_CLIENT_EXTERNAL_ID = '1dcad188-4c47-4939-9f0a-fb6802ef4f0d';

    public function run()
    {
        $owner    = User::query()->where('email', UsersTableSeeder::ADMIN_EMAIL)->first();
        $industry = Industry::query()->orderBy('id')->first();

        if ( ! $owner || ! $industry) {
            $this->command->warn('ClientsTableSeeder: Requires seeded admin user and at least one industry to exist. Skipping client creation.');

            return;
        }

        Client::query()->updateOrCreate(
            ['external_id' => self::SEED_CLIENT_EXTERNAL_ID],
            [
                'company_name' => self::SEED_CLIENT_NAME,
                'address'      => 'Seed Street 1',
                'zipcode'      => '1000',
                'city'         => 'Copenhagen',
                'vat'          => '12345678',
                'company_type' => 'ApS',
                'user_id'      => $owner->id,
                'industry_id'  => $industry->id,
            ]
        );
    }
}

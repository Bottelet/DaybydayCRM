<?php

namespace Database\Seeders;

use App\Models\BusinessHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('settings')->exists()) {
            return;
        }

        DB::table('settings')->insert([
            'id'             => 1,
            'client_number'  => 10000,
            'invoice_number' => 10000,
            'country'        => 'US',
            'company'        => 'Media',
            'max_users'      => 50,
            'vat'            => 0,
            'currency'       => 'USD',
            'language'       => 'en',
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($days as $day) {
            BusinessHour::query()->firstOrCreate(
                ['day' => $day, 'settings_id' => 1],
                ['open_time' => '09:00', 'close_time' => '18:00']
            );
        }
    }

    private function integerToDay()
    {
        return [
            1 => 'monday',
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday',
            7 => 'sunday',
        ];
    }
}

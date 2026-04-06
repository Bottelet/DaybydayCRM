<?php

<<<<<<< Updated upstream:database/seeders/SettingsTableSeeder.php
namespace Database\Seeders;

=======
>>>>>>> Stashed changes:database/seeds/SettingsTableSeeder.php
use App\Models\BusinessHour;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        DB::table('settings')->insert([
            0 => [
                'id' => 1,
                'client_number' => 10000,
                'invoice_number' => 10000,
                'country' => 'en',
                'company' => 'Media',
                'max_users' => 10,
                'created_at' => null,
                'updated_at' => null,
            ],
        ]);

        for ($i = 1; $i < 8; $i++) {
            BusinessHour::create([
                'day' => $this->integerToDay()[$i],
                'open_time' => '09:00',
                'close_time' => '18:00',
                'settings_id' => 1,
            ]);
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

<?php

use Illuminate\Database\Seeder;

class SettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('settings')->insert(array(
            0 =>
            array(
                'id' => 1,
                'client_number' => 10000,
                'invoice_number' => 10000,
                'country' => 'en',
                'company' => 'Media',
                'max_users' => 10,
                'created_at' => null,
                'updated_at' => null,
            ),
        ));

        for ($i=1; $i < 8; $i++) {
            \App\Models\BusinessHour::create([
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
            7 => 'sunday'
        ];
    }
}

<?php

use Illuminate\Database\Migrations\Migration;

class ChangeTypeToExtraInNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function ($table) {
            $driver = Config::get('database.driver');

            if ($driver === 'mysql' || $driver === 'sqlite')
            {
                DB::statement('ALTER TABLE notifications MODIFY COLUMN extra json');
            }
            elseif ($driver === 'pgsql')
            {
                DB::statement('ALTER TABLE notifications ALTER COLUMN extra TYPE json USING code::string');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function ($table) {

            $driver = Config::get('database.driver');

            if ($driver === 'mysql' || $driver === 'sqlite')
            {
                DB::statement('ALTER TABLE notifications MODIFY COLUMN extra STRING(255)');
            }
            elseif ($driver === 'pgsql')
            {
                DB::statement('ALTER TABLE notifications ALTER COLUMN extra TYPE string USING code::json');
            }
        });
    }
}

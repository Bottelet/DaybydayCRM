<?php

use Illuminate\Database\Migrations\Migration;

class AddExpireTimeColumnToNotificationTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function ($table) {
            $table->timestamp('expire_time')->nullable();
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
            $table->dropColumn('expire_time');
        });
    }
}

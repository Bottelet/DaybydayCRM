<?php

use Illuminate\Database\Migrations\Migration;

class AlterCategoryNameToUnique extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_categories', function ($table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_categories', function ($table) {
            $table->dropUnique('notification_categories_name_unique');
        });
    }
}

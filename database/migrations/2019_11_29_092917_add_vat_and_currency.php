<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVatAndCurrency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
<<<<<<< Updated upstream
        Schema::table('settings', static function (Blueprint $table) {
=======
        Schema::table('settings', function (Blueprint $table) {
>>>>>>> Stashed changes
            $table->string('currency', 3)->default('USD')->after('company');
            $table->smallInteger('vat')->default(725)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
<<<<<<< Updated upstream
        Schema::table('settings', static function (Blueprint $table) {
=======
        Schema::table('settings', function (Blueprint $table) {
>>>>>>> Stashed changes
            $table->dropColumn('currency');
            $table->dropColumn('vat');
        });
    }
}

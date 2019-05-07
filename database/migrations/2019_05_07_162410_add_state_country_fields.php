<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStateCountryFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('region')->nullable()->after('city');
            $table->string('country', 2)->default('US')->after('region');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('region')->nullable()->after('city');
            $table->string('country', 2)->default('US')->after('region');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('country');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('region');
            $table->dropColumn('country');
        });
    }
}

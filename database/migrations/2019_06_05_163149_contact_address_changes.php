<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ContactAddressChanges extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->renameColumn('address', 'address1');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->string('address2')->nullable()->default(null)->after('address1');
            $table->string('state')->nullable()->default(null)->after('address2');
            $table->string('country')->nullable()->default(null)->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('address2');
            $table->dropColumn('state');
            $table->dropColumn('country');
        });
    }
}

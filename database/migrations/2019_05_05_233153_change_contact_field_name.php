<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeContactFieldName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // must do this in two steps, since we're modifying the same column twice
        // can't change() and renameColumn in the same Schema::table.

        Schema::table('clients', function (Blueprint $table) {
            $table->string('name')->nullable(true)->default(null)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('name', 'primary_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // must do this in two steps, since we're modifying the same column twice
        // can't change() and renameColumn in the same Schema::table.

        Schema::table('clients', function (Blueprint $table) {
            $table->string('primary_contact_name')->nullable(false)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('primary_contact_name', 'name');
        });

    }
}

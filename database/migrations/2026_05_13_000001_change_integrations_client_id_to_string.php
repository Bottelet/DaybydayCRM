<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeIntegrationsClientIdToString extends Migration
{
    public function up()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->string('client_id', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->integer('client_id')->nullable()->change();
        });
    }
}

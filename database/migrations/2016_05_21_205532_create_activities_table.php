<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('activities', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id');
            $table->string('log_name')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_type')->nullable();
            $table->string('text');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('ip_address', 64);
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('activities');
    }
}

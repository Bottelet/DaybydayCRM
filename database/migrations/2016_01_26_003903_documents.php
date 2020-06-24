<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Documents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id');
            $table->string('size');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime');
            $table->string('integration_id')->nullable();
            $table->string('integration_type');
            $table->morphs('source');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        Schema::drop('documents');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

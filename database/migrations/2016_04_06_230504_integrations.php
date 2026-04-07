<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class Integrations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('client_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('client_secret')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_type')->nullable();
            $table->string('org_id')->nullable();
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
        Schema::drop('integrations');
    }
}

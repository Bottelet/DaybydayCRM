<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('note');
            $table->integer('status');
            $table->integer('fk_user_id_assign')->unsigned();
            $table->foreign('fk_user_id_assign')->references('id')->on('users');
            $table->integer('fk_client_id')->unsigned();
            $table->foreign('fk_client_id')->references('id')->on('clients');
            $table->integer('fk_user_id_created')->unsigned();
            $table->foreign('fk_user_id_created')->references('id')->on('users');
            $table->datetime('contact_date');
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
        Schema::drop('leads');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

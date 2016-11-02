<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
        
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('primary_number');
            $table->string('secondary_number');
            $table->string('address');
            $table->string('zipcode');
            $table->string('city');
            $table->string('company_name');
            $table->string('vat');
            $table->string('industry');
            $table->string('company_type');
            $table->integer('fk_user_id')->unsigned();
            $table->foreign('fk_user_id')->references('id')->on('users');
            $table->integer('industry_id')->unsigned();
            $table->foreign('industry_id')->references('id')->on('industries');
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
        Schema::drop('clients');
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}

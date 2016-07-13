<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('from_id')->index()->unsigned();
            $table->string('from_type')->index()->nullable();
            $table->bigInteger('to_id')->index()->unsigned();
            $table->string('to_type')->index()->nullable();
            $table->integer('category_id')->index()->unsigned();
            $table->string('url');
            $table->string('extra')->nullable();
            $table->tinyInteger('read')->default(0);
            $table->timestamps();

            $table->foreign('category_id')->references('id')
                  ->on('notification_categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('notifications');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        Schema::create('credit_lines', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id');
            $table->string('title');
            $table->text('comment');
            $table->integer('price');
            $table->integer('credit_note_id')->unsigned();
            $table->foreign('credit_note_id')->references('id')->on('credit_notes')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('product_id')->nullable();

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
        return;
        Schema::dropIfExists('credit_lines');
    }
}

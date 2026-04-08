<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
        Schema::create('credit_lines', static function (Blueprint $table) {
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

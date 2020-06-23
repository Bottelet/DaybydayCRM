<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLeadsTableSupportQualified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean("qualified")->index()->after("user_created_id")->default(false);
            $table->string("result")->after("qualified")->nullable();
            $table->integer('invoice_id')->unsigned()->nullable()->after("result");
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('qualified');
            $table->dropColumn('result');
            $table->dropColumn('invoice_id');
        });
    }
}

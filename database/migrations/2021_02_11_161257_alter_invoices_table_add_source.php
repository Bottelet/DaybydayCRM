<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesTableAddSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string("source_type")->after('integration_type');
            $table->unsignedBigInteger("source_id")->after('source_type');
            $table->index(["source_type", "source_id"]);
            $table->integer('sale_id')->unsigned()->nullable();
            $table->foreign('sale_id')->references('id')->on('sales');
            $table->string('offer_status')->nullable()->after('status');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('leads_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

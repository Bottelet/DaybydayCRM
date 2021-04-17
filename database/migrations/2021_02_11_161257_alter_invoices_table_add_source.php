<?php

use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterInvoicesTableAddSource extends Migration
{
    protected $invoiceLines;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string("source_type")->nullable()->after('integration_type');
            $table->unsignedBigInteger("source_id")->nullable()->after('source_type');
            $table->index(["source_type", "source_id"]);
            $table->integer('offer_id')->unsigned()->nullable()->after('client_id');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('set null');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign('leads_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign('tasks_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });
        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->integer('offer_id')->unsigned()->nullable()->after('price');
            $table->foreign('offer_id')->references('id')->on('offers')->onDelete('cascade');
            
            $this->invoiceLines = InvoiceLine::all();
            $table->dropForeign('invoice_lines_invoice_id_foreign');
            $table->dropColumn('invoice_id');    
        });

        Schema::table('invoice_lines', function (Blueprint $table) {
            $table->integer('invoice_id')->unsigned()->nullable()->after('price');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade'); 
            foreach($this->invoiceLines as $invoiceLine) {
                $invoiceLine->invoice_id = $invoiceLine->invoice_id;
                $invoiceLine->save();
            }  
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

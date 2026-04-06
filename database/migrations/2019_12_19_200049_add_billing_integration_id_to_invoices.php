<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBillingIntegrationIdToInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', static function (Blueprint $table) {
            $table->string('integration_invoice_id')->nullable()->after('due_at');
            $table->string('integration_type')->nullable()->after('integration_invoice_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', static function (Blueprint $table) {
            $table->dropColumn(['integration_invoice_id', 'integration_type']);
        });
    }
}

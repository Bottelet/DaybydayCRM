<?php

use App\Models\Permissions;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoicePermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * Add permission
         */
        $createContact               = new Permissions();
        $createContact->display_name = 'See invoices';
        $createContact->name         = 'invoice';
        $createContact->description  = 'Permission to see invoices';
        $createContact->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Permissions::where('name', 'invoice')->delete();
    }
}

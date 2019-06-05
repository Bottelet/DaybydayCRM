<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ClientAddresses extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('company_name', 'name');
            $table->renameColumn('address', 'billing_address1');
            $table->renameColumn('zipcode', 'billing_zipcode');
            $table->renameColumn('city', 'billing_city');
            $table->renameColumn('email', 'primary_email');
            $table->dropColumn('primary_contact_name');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->string('billing_address2')->nullable()->default(null)->after('billing_address1');
            $table->string('billing_state')->nullable()->default(null)->after('billing_city');
            $table->string('billing_country')->nullable()->default(null)->after('billing_zipcode');
            $table->string('shipping_address1')->nullable()->default(null)->after('billing_country');
            $table->string('shipping_address2')->nullable()->default(null)->after('shipping_address1');
            $table->string('shipping_city')->nullable()->default(null)->after('shipping_address2');
            $table->string('shipping_state')->nullable()->default(null)->after('shipping_city');
            $table->string('shipping_zipcode')->nullable()->default(null)->after('shipping_state');
            $table->string('shipping_country')->nullable()->default(null)->after('shipping_zipcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->renameColumn('name', 'company_name');
            $table->renameColumn('billing_address1', 'address');
            $table->dropcolumn('billing_address2');
            $table->renameColumn('billing_zipcode', 'zipcode');
            $table->renameColumn('billing_city', 'city');
            $table->dropColumn('billing_state');
            $table->dropColumn('billing_country');
            $table->dropColumn('shipping_address1');
            $table->dropColumn('shipping_address2');
            $table->dropColumn('shipping_city');
            $table->dropColumn('shipping_state');
            $table->dropColumn('shipping_zipcode');
            $table->dropColumn('shipping_country');
            $table->renameColumn('primary_email', 'email');
            $table->string('primary_contact_name');
        });
    }
}

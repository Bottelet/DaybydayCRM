<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Only drop the column if it exists
        if (Schema::hasColumn('invoices', 'payment_received_at')) {
            Schema::table('invoices', static function (Blueprint $table) {
                $table->dropColumn('payment_received_at');
            });
        }

        Schema::create('payments', static function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id')->default('');
            $table->integer('amount');
            $table->string('description')->nullable();
            $table->string('payment_source');
            $table->date('payment_date');
            $table->string('integration_payment_id')->nullable();
            $table->string('integration_type')->nullable();
            $table->integer('invoice_id')->unsigned();
            $table->foreign('invoice_id')->references('id')->on('invoices');

            $table->softDeletes();
            $table->timestamps();
        });

        /** Create new permissions */
        $cpp = Permission::create([
            'external_id'  => Str::uuid()->toString(),
            'display_name' => 'Add payment',
            'name'         => 'payment-create',
            'description'  => 'Be able to add a new payment on a invoice',
            'grouping'     => 'payment',
        ]);

        $dpp = Permission::create([
            'external_id'  => Str::uuid()->toString(),
            'display_name' => 'Delete payment',
            'name'         => 'payment-delete',
            'description'  => 'Be able to delete a payment',
            'grouping'     => 'payment',
        ]);

        $roles = Role::where('name', 'owner')->get();
        foreach ($roles as $role) {
            $role->permissions()->attach([$cpp->id, $dpp->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', static function ($table) {
            $table->dateTime('payment_received_at')->nullable();
        });
        $cpp   = Permission::where('name', 'payment-create')->first();
        $dpp   = Permission::where('name', 'payment-delete')->first();
        $roles = Role::where('name', 'owner')->get();
        foreach ($roles as $role) {
            $role->permissions()->detach([$cpp->id, $dpp->id]);
        }
        $cpp->forceDelete();
        $dpp->forceDelete();
        Schema::dropIfExists('payments');
    }
}

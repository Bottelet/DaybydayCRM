<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        return;
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id');

            $table->softDeletes();
            $table->integer('invoice_id')->unsigned();
            $table->foreign('invoice_id')->references('id')->on('invoices');
            $table->timestamps();
        });

        /** Create new permissions */
        $cpp = Permission::create([
            'display_name' => 'Create credit note',
            'name' => 'credit-note-create',
            'description' => 'Be able to add a credit note for an invoice',
            'grouping' => 'credit-note',
        ]);

        $dpp = Permission::create([
            'display_name' => 'Delete credit note',
            'name' => 'credit-note-delete',
            'description' => 'Be able to delete a credit note',
            'grouping' => 'credit-note',
        ]);

        $roles = \App\Models\Role::where('name', 'owner')->get();
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
        return;
        $cpp = Permission::where('name', 'credit-note-create')->firstOrFail();
        $dpp = Permission::where('name', 'credit-note-delete')->firstOrFail();
        $roles = \App\Models\Role::where('name', 'owner')->get();
        foreach ($roles as $role) {
            $role->permissions()->detach([$cpp->id, $dpp->id]);
        }
        $cpp->forceDelete();
        $dpp->forceDelete();
        Schema::dropIfExists('credit_notes');
    }
}

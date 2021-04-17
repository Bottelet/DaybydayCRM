<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id');
            $table->dateTime('sent_at')->nullable();
            $table->nullableMorphs('source');
            $table->integer('client_id')->unsigned();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->string('status');
            $table->softDeletes();
            $table->timestamps();
        });

        $p1 = Permission::create([
            'display_name' => 'Add offer',
            'name' => 'offer-create',
            'description' => 'Be able to create an offer',
            'grouping' => 'offer',
        ]);

        $p2 = Permission::create([
            'display_name' => 'Edit offer',
            'name' => 'offer-edit',
            'description' => 'Be able to edit an offer',
            'grouping' => 'offer',
        ]);

        $p3 = Permission::create([
            'display_name' => 'Delete offer',
            'name' => 'offer-delete',
            'description' => 'Be able to delete an offer',
            'grouping' => 'offer',
        ]);

        $roles = \App\Models\Role::whereIn('name', ['owner', 'administrator'])->get();
        foreach ($roles as $role) {
            $role->permissions()->attach([$p1->id, $p2->id, $p3->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offers');
    }
}

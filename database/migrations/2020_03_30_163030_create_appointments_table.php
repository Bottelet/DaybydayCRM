<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string("external_id");
            $table->string("title");
            $table->string("description")->nullable();
            $table->nullableMorphs("source");
            $table->string("color", 10);
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->integer('client_id')->unsigned()->nullable();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->timestamp("start_at")->nullable();
            $table->timestamp("end_at")->nullable();

            $table->timestamps();
        });

        /** Create new permissions */
        $scpp = Permission::create([
            'display_name' => 'View calendar',
            'name' => 'calendar-view',
            'description' => 'Be able to view the calendar for appointments',
            'grouping' => 'appointment',
        ]);
        /** Create new permissions */
        $cpp = Permission::create([
            'display_name' => 'Add appointment',
            'name' => 'appointment-create',
            'description' => 'Be able to create a new appointment for a user',
            'grouping' => 'appointment',
        ]);

        /** Create new permissions */
        $epp = Permission::create([
            'display_name' => 'Edit appointment',
            'name' => 'appointment-edit',
            'description' => 'Be able to edit appointment such as times and title',
            'grouping' => 'appointment',
        ]);

        $dpp = Permission::create([
            'display_name' => 'Delete appointment',
            'name' => 'appointment-delete',
            'description' => 'Be able to delete an appointment',
            'grouping' => 'appointment',
        ]);

        $roles = \App\Models\Role::whereIn('name', ['owner', 'administrator'])->get();
        foreach ($roles as $role) {
            $role->permissions()->attach([$cpp->id, $dpp->id, $epp->id, $scpp->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments');
    }
}

<?php

use App\Models\Permission;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAbsencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('external_id');
            $table->string('reason');
            $table->text('comment')->nullable();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->boolean('medical_certificate')->nullable()->default(null);
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')
                ->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });

        /** Create new permissions */
        $acpp = Permission::create([
            'display_name' => 'Manage absences',
            'name' => 'absence-manage',
            'description' => 'Be able to manage absences for all users',
            'grouping' => 'hr',
        ]);

        /** Create new permissions */
        $vcpp = Permission::create([
            'display_name' => 'View absences',
            'name' => 'absence-view',
            'description' => 'Be able to view absences for all users and see who is absent today on the dashboard',
            'grouping' => 'hr',
        ]);

        $roles = \App\Models\Role::whereIn('name', ['owner', 'administrator'])->get();
        foreach ($roles as $role) {
            $role->permissions()->attach([$acpp->id, $vcpp->id]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('absences');
    }
}

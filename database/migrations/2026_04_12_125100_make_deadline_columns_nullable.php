<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeDeadlineColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('deadline')->nullable()->change();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->date('deadline')->nullable()->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->date('deadline')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->date('deadline')->nullable(false)->change();
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->date('deadline')->nullable(false)->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->date('deadline')->nullable(false)->change();
        });
    }
}

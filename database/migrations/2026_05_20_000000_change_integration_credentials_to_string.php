<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * The original integrations migration defined client_id and client_secret as
 * integer columns. Integration credentials are strings (OAuth client IDs,
 * secrets, API keys, etc.), so this migration changes those columns to the
 * correct string type.
 */
return new class () extends Migration {
    public function up(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->string('client_id')->nullable()->change();
            $table->string('client_secret')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->integer('client_id')->nullable()->change();
            $table->integer('client_secret')->nullable()->change();
        });
    }
};

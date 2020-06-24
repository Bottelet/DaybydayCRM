<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLanguageOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('card_brand');
            $table->dropColumn('stripe_id');
            $table->dropColumn('card_last_four');
            $table->dropColumn('trial_ends_at');
            $table->string("language", 2)->default("EN")->after("remember_token");
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string("language", 2)->default("EN")->after("max_users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('card_brand');
            $table->string('stripe_id');
            $table->string('card_last_four');
            $table->timestamp('trial_ends_at');
            $table->dropColumn("language");
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn("language");
        });
    }
}

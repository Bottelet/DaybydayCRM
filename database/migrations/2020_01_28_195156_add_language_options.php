<?php

use App\Traits\DropColumnsIfExist;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLanguageOptions extends Migration
{
    use DropColumnsIfExist;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $this->dropColumnIfExists('users', $table, [
                'card_brand',
                'stripe_id',
                'card_last_four',
                'trial_ends_at',
            ]);
            $table->string('language', 2)->default('EN')->after('remember_token');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->string('language', 2)->default('EN')->after('max_users');
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
            $table->dropColumn('language');
        });
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('language');
        });
    }
}

<?php

<<<<<<< Updated upstream
use App\Traits\DropColumnsIfExist;
=======
>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
            $this->dropColumnIfExists('users', $table, [
                'card_brand',
                'stripe_id',
                'card_last_four',
                'trial_ends_at',
            ]);
=======
            $table->dropColumn('card_brand');
            $table->dropColumn('stripe_id');
            $table->dropColumn('card_last_four');
            $table->dropColumn('trial_ends_at');
>>>>>>> Stashed changes
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

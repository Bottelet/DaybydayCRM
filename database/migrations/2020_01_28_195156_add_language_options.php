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
        // Drop columns from users table only if they exist, using trait
        Schema::table('users', function (Blueprint $table) {
            $this->dropColumnIfExists('users', $table, ['card_brand', 'stripe_id', 'card_last_four', 'trial_ends_at']);
        });
        // Add language column if it does not exist
        if (! Schema::hasColumn('users', 'language')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('language', 2)->default('EN')->after('remember_token');
            });
        }
        // Add language column to settings if it does not exist
        if (! Schema::hasColumn('settings', 'language')) {
            Schema::table('settings', function (Blueprint $table) {
                $table->string('language', 2)->default('EN')->after('max_users');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Add columns back to users table if they do not exist
        if (! Schema::hasColumn('users', 'card_brand')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('card_brand')->nullable();
            });
        }
        if (! Schema::hasColumn('users', 'stripe_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('stripe_id')->nullable();
            });
        }
        if (! Schema::hasColumn('users', 'card_last_four')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('card_last_four')->nullable();
            });
        }
        if (! Schema::hasColumn('users', 'trial_ends_at')) {
            Schema::table('users', function (Blueprint $table) {
                $table->timestamp('trial_ends_at')->nullable();
            });
        }
        // Drop language column from users if it exists
        Schema::table('users', function (Blueprint $table) {
            $this->dropColumnIfExists('users', $table, 'language');
        });
        // Drop language column from settings if it exists
        Schema::table('settings', function (Blueprint $table) {
            $this->dropColumnIfExists('settings', $table, 'language');
        });
    }
}

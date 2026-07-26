<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterLeadsTableSupportQualified extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        Schema::table('leads', static function (Blueprint $table) use ($isSqlite) {
            $table->boolean('qualified')->index()->after('user_created_id')->default(false);
            $table->string('result')->after('qualified')->nullable();
            $table->integer('invoice_id')->unsigned()->nullable()->after('result');
            if ( ! $isSqlite) {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $isSqlite = DB::getDriverName() === 'sqlite';

        Schema::table('leads', static function (Blueprint $table) use ($isSqlite) {
            $table->dropColumn('qualified');
            $table->dropColumn('result');
            if ( ! $isSqlite) {
                $table->dropForeign(['invoice_id']);
            }
            $table->dropColumn('invoice_id');
        });
    }
}

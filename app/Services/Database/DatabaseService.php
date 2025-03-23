<?php
namespace App\Services\Database;

use Illuminate\Support\Facades\DB;

class DatabaseService
{

    public function truncateAllExcept()
    {
        $excludedTables = explode(',', env('EXCLUDED_TABLES', ''));

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $tables = DB::select('SHOW TABLES');
        $tableKey = 'Tables_in_' . env('DB_DATABASE');

        foreach ($tables as $table) {
            $tableName = $table->$tableKey;

            if (!in_array($tableName, $excludedTables)) {
                DB::table($tableName)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
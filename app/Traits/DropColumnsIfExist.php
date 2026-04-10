<?php

namespace App\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait DropColumnsIfExist
{
    public function dropColumnIfExists(string $table, Blueprint $blueprint, string|array $columns): void
    {
        $columns = (array) $columns;

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                if (config('database.default') === 'sqlite') {
                    $this->dropIndexIfExists($table, $blueprint, $column);
                }
                $blueprint->dropColumn($column);
            }
        }
    }

    private function dropIndexIfExists(string $table, Blueprint $blueprint, string $column): void
    {
        $connection = Schema::getConnection();
        $schemaManager = $connection->getSchemaBuilder()->getConnection()->getDoctrineSchemaManager();
        $indexes = $schemaManager->listTableIndexes($table);

        foreach ($indexes as $index) {
            if (in_array($column, $index->getColumns())) {
                $blueprint->dropIndex($index->getName());
            }
        }
    }
}

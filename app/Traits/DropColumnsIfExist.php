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
                $blueprint->dropColumn($column);
            }
        }
    }
}

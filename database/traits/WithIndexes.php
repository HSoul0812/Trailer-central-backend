<?php

namespace Database\traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

trait WithIndexes
{
    public function indexExists(string $tableName, string $index): bool
    {
        $indexExists = DB::select(DB::raw("SHOW INDEX FROM $tableName WHERE Key_name = '$index'"));
        return !empty($indexExists);
    }

    public function dropIndexIfExist(string $tableName, string $index): void
    {
        if ($this->indexExists($tableName, $index)) {
            Schema::table($tableName, function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        }
    }
}
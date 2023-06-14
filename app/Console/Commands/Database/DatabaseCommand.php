<?php

namespace App\Console\Commands\Database;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class DatabaseCommand extends Command
{
    public function tableExists(string $tableName): bool
    {
        return Schema::hasTable($tableName);
    }

    public function cloneTable(string $existingTable, string $newTable): bool
    {
        return DB::statement("CREATE TABLE $newTable LIKE $existingTable");
    }

    public function migrateTableData(string $existingTable, string $newTable): bool
    {
        return DB::statement("INSERT INTO $newTable SELECT * FROM $existingTable");
    }

    public function renameTable(string $existingTable, string $newTable): bool
    {
        $randomStr = Str::random(5);
        $newTableSafeName = $newTable . $randomStr;
        return DB::statement("RENAME TABLE $newTable TO $newTableSafeName, $existingTable TO $newTable,$newTableSafeName TO $existingTable");
    }

    public function dropTable(string $table)
    {
        DB::statement('SET foreign_key_checks = 0');
        DB::statement("DROP TABLE $table");
        DB::statement('SET foreign_key_checks = 1');
    }
}


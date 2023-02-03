<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class MysqlUpgradePart1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $this->alterRowFormat();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        // Reverse the migrations
    }

    /**
     * @return string
     */
    private static function excludeRowFormatChangeTables(): string
    {
        return implode(', ', [
            '"gitea"',
            '"htw"',
            '"mediawiki"',
            '"mysql"',
            '"pma_metadata"',
            '"third_party_mirror"',
        ]);
    }

    /**
     * Alter all tables having `row_format` as `fixed` to `dynamic`
     *
     * @return void
     */
    private function alterRowFormat(): void
    {
        try {
            $rowFormatTables = DB::select(DB::raw("SELECT
                CONCAT('ALTER TABLE `', TABLE_SCHEMA, '`.`', TABLE_NAME, '` ', 'ROW_FORMAT=DYNAMIC;') AS _alter
            FROM
                INFORMATION_SCHEMA.TABLES
            WHERE
                `ENGINE` = 'InnoDB'
                AND `ROW_FORMAT` != 'DYNAMIC'
                AND `TABLE_SCHEMA` NOT IN (" . self::excludeRowFormatChangeTables() . ")
                LIMIT 1;
            "));

            $rowFormatTables = Arr::pluck($rowFormatTables, '_alter');

            foreach ($rowFormatTables as $query) {
                DB::statement($query);
            }
        } catch (Exception $exception) {
            Log::info($exception->getMessage(), $exception->getTrace());
        }
    }
}

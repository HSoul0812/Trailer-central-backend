<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCsvUrlToCollectorTable extends Migration
{
    private const tableName = 'collector';
    private const columnName = 'csv_url';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        if (Schema::hasColumn(self::tableName, self::columnName)) {
            Schema::table(self::tableName, function (Blueprint $table) {
                $table->string(self::columnName)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        if (Schema::hasColumn(self::tableName, self::columnName)) {
            Schema::table(self::tableName, function (Blueprint $table) {
                $table->dropColumn(self::columnName);
            });
        }
    }
}

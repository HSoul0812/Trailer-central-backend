<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowOnAuctionOptOnCollectorTable extends Migration
{
    private const tableName = 'collector';
    private const columnName = 'show_on_auction123';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        if (!Schema::hasColumn(self::tableName, self::columnName)) {
            Schema::table(self::tableName, function (Blueprint $table) {
                $table->tinyInteger(self::columnName)->default(0)->nullable();
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

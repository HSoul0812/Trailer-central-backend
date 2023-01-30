<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ExpandFtpPathCollector extends Migration
{

    private const TARGET_TABLE = 'collector';
    private const TARGET_FIELD = 'ftp_path';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        Schema::table(self::TARGET_TABLE, function (Blueprint $table) {
            if ($this->checkColumn()) {
                $table->string(self::TARGET_FIELD, 254)->change();
            } else {
                $table->string(self::TARGET_FIELD, 254);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        Schema::table(self::TARGET_TABLE, function (Blueprint $table) {
                $table->string(self::TARGET_FIELD, 128)->change();
        });
    }

    /**
     * Validate column existence on migrate
     * @return bool
     */
    private function checkColumn(): bool {
        return Schema::hasColumn(self::TARGET_TABLE, self::TARGET_FIELD);
    }
}

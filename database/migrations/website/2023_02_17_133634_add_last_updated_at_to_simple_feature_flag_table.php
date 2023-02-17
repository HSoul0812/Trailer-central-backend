<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastUpdatedAtToSimpleFeatureFlagTable extends Migration
{
    const TARGET_TABLE = 'simple_feature_flag';
    const TARGET_COLUMN = 'last_updated_at';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(self::TARGET_TABLE, function (Blueprint $table) {
            if (!$this->checkColumn()) {
                $table->timestamp(self::TARGET_COLUMN)->useCurrent();
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
        Schema::table(self::TARGET_TABLE, function (Blueprint $table) {
            if ($this->checkColumn()) {
                $table->dropColumn(self::TARGET_COLUMN);
            }
        });
    }

    /**
     * Validate column existence on migrate
     * @return bool
     */
    private function checkColumn(): bool {
        return Schema::hasColumn(self::TARGET_TABLE, self::TARGET_COLUMN);
    }
}

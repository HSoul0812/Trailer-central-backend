<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToInventoryIdInDmsUnitSaleTradeInV1Table extends Migration
{
    private $tableName = 'dms_unit_sale_trade_in_v1';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->index(['inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->tableName, function (Blueprint $table) {
            $table->dropIndex(['inventory_id']);
        });
    }
}

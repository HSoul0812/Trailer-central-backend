<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToInventoryIdRepairOrder extends Migration
{
    private const INVENTORY_ID_INDEX_NAME = 'INVENTORY_LOOKUP';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->index('inventory_id', self::INVENTORY_ID_INDEX_NAME);
        });
    } 

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_repair_order', function (Blueprint $table) {
            $table->dropIndex(self::INVENTORY_ID_INDEX_NAME);
        });
    }
} 

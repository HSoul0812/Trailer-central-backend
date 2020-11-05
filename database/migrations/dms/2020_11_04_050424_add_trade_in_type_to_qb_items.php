<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddTradeInTypeToQbItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','deposit_down_payment','income_down_payment','trade_in') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");

        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->integer('inventory_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','deposit_down_payment','income_down_payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");

        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->dropColumn('inventory_id');
        });
    }
}

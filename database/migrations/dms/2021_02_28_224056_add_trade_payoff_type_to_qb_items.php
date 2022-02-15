<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTradePayoffTypeToQbItems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','deposit_down_payment','income_down_payment','trade_in','trade_in_payoff') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','deposit_down_payment','income_down_payment','trade_in') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ModifyFeeVisibilityClass extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `dealer_location_quote_fee` CHANGE `visibility` `visibility` enum('hidden','visible','visible_locked','visible_pos','visible_locked_pos') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `dealer_location_quote_fee` CHANGE `visibility` `visibility` enum('hidden','visible','visible_locked') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }
}

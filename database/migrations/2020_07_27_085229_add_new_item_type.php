<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNewItemType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined','down_payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `qb_items` CHANGE `type` `type` ENUM('trailer','part','labor','add_on','discount','tax','undefined') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'undefined';");
    }
}

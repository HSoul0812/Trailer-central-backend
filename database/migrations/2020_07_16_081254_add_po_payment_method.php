<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPoPaymentMethod extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `qb_payment_methods` CHANGE `type` `type` ENUM('credit_card','cash','check','eft','financing','po') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cash';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `qb_payment_methods` CHANGE `type` `type` ENUM('credit_card','cash','check','eft','financing') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'cash';");
    }
}

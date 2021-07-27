<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddQuoteOptionToRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `dealer_refunds` CHANGE `tb_name` `tb_name` ENUM('qb_payment', 'crm_pos_sales', 'dms_unit_sale') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `dealer_refunds` CHANGE `tb_name` `tb_name` ENUM('qb_payment', 'crm_pos_sales') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }
}

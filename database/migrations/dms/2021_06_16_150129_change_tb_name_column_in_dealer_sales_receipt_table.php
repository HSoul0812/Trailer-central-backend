<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeTbNameColumnInDealerSalesReceiptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `dealer_sales_receipt` CHANGE `tb_name` `tb_name` ENUM('qb_payment', 'crm_pos_sales', 'dealer_refunds') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE `dealer_sales_receipt` CHANGE `tb_name` `tb_name` ENUM('qb_payment', 'crm_pos_sales') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }
}

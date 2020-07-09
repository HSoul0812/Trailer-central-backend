<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateQuickbookApproval extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE `quickbook_approval`
                CHANGE `tb_name` `tb_name` ENUM('qb_bills','qb_bill_payment','qb_items','qb_vendors','dealer_employee','qb_invoices','dms_customer','qb_payment','crm_pos_sales','dms_unit_sale','dms_unit_sale_payment','qb_accounts','qb_item_category','qb_payment_methods','qb_journal_entry','qb_expenses','qb_items_new','inventory_floor_plan_payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE `quickbook_approval`
                CHANGE `tb_name` `tb_name` ENUM('qb_bills','qb_bill_payment','qb_items','qb_vendors','dealer_employee','qb_invoices','dms_customer','qb_payment','crm_pos_sales','dms_unit_sale','dms_unit_sale_payment','qb_accounts','qb_item_category','qb_payment_methods','qb_journal_entry','qb_expenses','qb_items_new') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;"
        );
    }
}

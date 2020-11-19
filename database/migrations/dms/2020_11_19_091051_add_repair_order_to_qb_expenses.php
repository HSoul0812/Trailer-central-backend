<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRepairOrderToQbExpenses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE
                      `qb_expenses`
                   MODIFY COLUMN
                      `tb_name` enum('qb_invoices','crm_pos_sales','dms_repair_order') NOT NULL;"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE
                      `qb_expenses`
                   MODIFY COLUMN
                      `tb_name` enum('qb_invoices','crm_pos_sales') NOT NULL;"
        );
    } 
}

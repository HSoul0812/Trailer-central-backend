<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddDiffAmountToRegister extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_pos_register', function (Blueprint $table) {
            $table->decimal('diff_amount', 10, 2)->nullable();
        });

        $platform = Schema::getConnection()->getDoctrineSchemaManager()->getDatabasePlatform();
        $platform->registerDoctrineTypeMapping('enum', 'string');

        DB::statement("ALTER TABLE `qb_expenses` CHANGE `tb_name` `tb_name` ENUM('qb_invoices','crm_pos_sales','dms_repair_order','inventory_floor_plan_payment','crm_pos_register') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_pos_register', function (Blueprint $table) {
            $table->dropColumn('diff_amount');
        });

        DB::statement("ALTER TABLE `qb_expenses` CHANGE `tb_name` `tb_name` ENUM('qb_invoices','crm_pos_sales','dms_repair_order','inventory_floor_plan_payment') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");
    }
}

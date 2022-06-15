<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddNewColumnLocalTaxToDMSUnitSale extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            ALTER TABLE `dms_unit_sale`
            ADD COLUMN `use_local_tax` VARCHAR(1) NOT NULL DEFAULT 0 AFTER `title`
        ");

        DB::statement("
            UPDATE `dms_unit_sale`, `dealer_location_sales_tax`
            SET `dms_unit_sale`.`use_local_tax` = `dealer_location_sales_tax`.`use_local_tax`
            WHERE `dms_unit_sale`.`dealer_location_id` = `dealer_location_sales_tax`.`dealer_location_id`
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->dropColumn('use_local_tax');
        });
    }
}

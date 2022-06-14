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
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->string('use_local_tax', 2)->after('title')->default(0);
        });

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

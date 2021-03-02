<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostOfUnitToDmsUnitSaleTradeInV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->float('temp_inv_cost_of_unit')->after('temp_inv_price')->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->dropColumn('temp_inv_cost_of_unit');
        });
    }
}

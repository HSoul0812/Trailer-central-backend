<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DealerLocationTaxAddShopEnvBasis extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('dealer_location_sales_tax', function (Blueprint $table) {
            //
            $table->enum('shop_supply_basis', [
                'parts_and_labor',
                'parts',
                'labor',
                'flat',
            ])->after('labor_tax_type');

            //
            $table->enum('env_fee_basis', [
                'parts_and_labor',
                'parts',
                'labor',
                'flat',
            ])->after('shop_supply_cap');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('dealer_location_sales_tax', function (Blueprint $table) {
            //
            $table->dropColumn('shop_supply_basis');
            $table->dropColumn('env_fee_basis');
        });
    }
}

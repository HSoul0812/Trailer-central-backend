<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\CRM\Dms\UnitSale\TradeIn;

class AddInventoryImmediatelyToUnitSaleTradeInTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->boolean('add_inventory_immediately')->default(TradeIn::DO_NOT_ADD_INVENTORY_IMMEDIATELY);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale_fee', function (Blueprint $table) {
            $table->dropColumn('add_inventory_immediately');
        });
    }
}

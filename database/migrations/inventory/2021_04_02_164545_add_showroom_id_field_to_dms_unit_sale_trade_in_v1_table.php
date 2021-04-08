<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowroomIdFieldToDmsUnitSaleTradeInV1Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_trade_in_v1', function (Blueprint $table) {
            $table->integer('showroom_id')->nullable();
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
            $table->dropColumn('showroom_id');
        });
    }
}

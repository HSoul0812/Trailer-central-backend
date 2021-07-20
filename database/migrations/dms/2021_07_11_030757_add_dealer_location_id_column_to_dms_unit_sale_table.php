<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerLocationIdColumnToDmsUnitSaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->unsignedInteger('dealer_location_id')
                ->after('title');
        });

        // Copy sale_location_id in dealer_location_id column
        \DB::table('dms_unit_sale')->update(['dealer_location_id' => \DB::raw('sales_location_id')]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale', function (Blueprint $table) {
            $table->dropColumn('dealer_location_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexForDealerLocationIdOnDealerSalesHistory extends Migration
{
    
    /**
     * Run the migrations.
     *
     * @return void 
     */
    public function up()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) { 
            $table->index(['dealer_location_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_sales_history', function (Blueprint $table) {
            $table->dropIndex(['dealer_location_id']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippingToSalesTaxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_location_sales_tax', function (Blueprint $table) {
            $table->tinyInteger('is_shipping_taxed')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_location_sales_tax', function (Blueprint $table) {
            $table->dropColumn('is_shipping_taxed');
        });
    }
}

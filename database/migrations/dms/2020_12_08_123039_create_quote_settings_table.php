<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuoteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_quote_settings', function (Blueprint $table) {
            //
            $table->bigIncrements('id');
            $table->unsignedInteger('dealer_id')->unique();
            $table->tinyInteger('include_inventory_for_sales_tax')->default(1);
            $table->tinyInteger('include_part_for_sales_tax')->default(1);
            $table->tinyInteger('include_labor_for_sales_tax')->default(0);
            $table->tinyInteger('include_fees_for_sales_tax')->comment('It only includes State Taxed Fees')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dealer_quote_settings');
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerLocationMileageFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_location_mileage_fee', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('dealer_location_id');
            $table->integer('inventory_category_id');
            $table->decimal('fee_per_mile', 9, 2);
            $table->foreign('dealer_location_id')->on('dealer_location')->references('dealer_location_id');
            $table->foreign('inventory_category_id')->on('inventory_category')->references('inventory_category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealer_location_mileage_fee');
    }
}

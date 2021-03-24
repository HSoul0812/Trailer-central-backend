<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealerMfgSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dealer_mfg_setting', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('dealer_id');
            $table->unsignedInteger('inventory_mfg_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('vendor_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dealer_mfg_setting');
    }
}

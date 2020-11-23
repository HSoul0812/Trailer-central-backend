<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartsV1ShippingHandlingPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add Columns to Parts
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->decimal('shipping_fee', 9, 2)->after('msrp')->nullable();
            $table->tinyInteger('use_handling_fee')->after('shipping_fee')->nullable();
            $table->decimal('handling_fee', 9, 2)->after('use_handling_fee')->nullable();
            $table->tinyInteger('fulfillment_type')->after('handling_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop Columns From Parts
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->dropColumn('shipping_fee');
            $table->dropColumn('use_handling_fee');
            $table->dropColumn('handling_fee');
            $table->dropColumn('fulfillment_type');
        });
    }
}
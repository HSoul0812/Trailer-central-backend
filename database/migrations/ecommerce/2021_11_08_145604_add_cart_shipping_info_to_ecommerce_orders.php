<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartShippingInfoToEcommerceOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->string('shipping_carrier_code', 20)
                ->after('shipping_region')
                ->index();
            $table->string('shipping_method_code', 20)
                ->after('shipping_carrier_code')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_carrier_code', 'shipping_method_code']);
        });
    }
}

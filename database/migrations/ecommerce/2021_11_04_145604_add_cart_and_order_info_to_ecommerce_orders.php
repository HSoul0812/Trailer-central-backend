<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCartAndOrderInfoToEcommerceOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->integer('ecommerce_customer_id')
                ->unsigned()
                ->after('refunded_parts')
                ->nullable()
                ->index();
            $table->string('ecommerce_cart_id', 50)->after('ecommerce_customer_id')->index();
            $table->integer('ecommerce_order_id')
                ->unsigned()
                ->after('ecommerce_cart_id')
                ->nullable()
                ->index();
            $table->text('ecommerce_items')
                ->after('ecommerce_order_id')
                ->nullable()
                ->comment('A valid json array');
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
            $table->dropColumn(['ecommerce_customer_id', 'ecommerce_cart_id', 'ecommerce_order_id', 'ecommerce_items']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMagentoRefundedAmountsToEcommerceCompletedOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->decimal('parts_refunded_amount', 10)
                ->unsigned()
                ->after('total_refunded_amount')
                ->nullable();

            $table->decimal('shipping_refunded_amount', 10)
                ->unsigned()
                ->after('parts_refunded_amount')
                ->nullable();

            $table->decimal('handling_refunded_amount', 10)
                ->unsigned()
                ->after('shipping_refunded_amount')
                ->nullable();

            $table->decimal('adjustment_refunded_amount', 10)
                ->unsigned()
                ->after('handling_refunded_amount')
                ->nullable()
                ->comment('custom refunded amount');

            $table->decimal('tax_refunded_amount', 10)
                ->unsigned()
                ->after('adjustment_refunded_amount')
                ->nullable();
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
            $table->dropColumn([
                'parts_refunded_amount',
                'shipping_refunded_amount',
                'handling_refunded_amount',
                'adjustment_refunded_amount',
                'tax_refunded_amount'
            ]);
        });
    }
}

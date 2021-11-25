<?php

use Illuminate\Database\Migrations\Migration;

class RenameRefundedAmountInEcommerceCompletedOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ecommerce_completed_orders CHANGE refunded_amount total_refunded_amount DECIMAL(10,2) UNSIGNED NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ecommerce_completed_orders CHANGE total_refunded_amount refunded_amount DECIMAL(10,2) UNSIGNED NULL;");
    }
}

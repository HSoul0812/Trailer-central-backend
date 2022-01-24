<?php

use Illuminate\Database\Migrations\Migration;

class RenameAmountOnEcommerceOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ecommerce_order_refunds CHANGE amount total_amount DECIMAL(10,2) UNSIGNED NOT NULL;");

        DB::statement("ALTER TABLE ecommerce_order_refunds CHANGE object_id payment_gateway_id VARCHAR(255) NULL;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ecommerce_order_refunds CHANGE total_amount amount DECIMAL(10,2) UNSIGNED NOT NULL;");

        DB::statement("ALTER TABLE ecommerce_order_refunds CHANGE payment_gateway_id object_id VARCHAR(255) NULL;");
    }
}

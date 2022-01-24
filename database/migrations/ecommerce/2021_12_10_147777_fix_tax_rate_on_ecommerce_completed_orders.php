<?php

use Illuminate\Database\Migrations\Migration;

class FixTaxRateOnEcommerceCompletedOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement(<<<SQL
                        ALTER TABLE ecommerce_completed_orders
                        MODIFY tax_rate DECIMAL(4, 4) NULL;
SQL
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement(<<<SQL
                        ALTER TABLE ecommerce_completed_orders
                        MODIFY tax_rate DECIMAL(10, 2) NULL;
SQL
        );
    }
}

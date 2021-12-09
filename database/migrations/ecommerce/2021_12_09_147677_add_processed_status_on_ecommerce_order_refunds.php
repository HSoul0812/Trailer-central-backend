<?php

use Illuminate\Database\Migrations\Migration;

class AddProcessedStatusOnEcommerceOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement(<<<SQL
                        ALTER TABLE `ecommerce_order_refunds` CHANGE `status` `status`
                                ENUM('pending', 'denied', 'approved', 'processing', 'processed', 'completed', 'failed')
                                NOT NULL DEFAULT 'pending';
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
                        ALTER TABLE `ecommerce_order_refunds` CHANGE `status` `status`
                                ENUM('pending', 'denied', 'approved', 'processing', 'completed', 'failed')
                                NOT NULL DEFAULT 'pending';
SQL
        );
    }
}

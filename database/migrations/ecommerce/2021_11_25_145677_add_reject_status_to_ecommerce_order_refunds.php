<?php

use Illuminate\Database\Migrations\Migration;

class AddRejectStatusToEcommerceOrderRefunds extends Migration
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
                                ENUM(
                                    'pending',
                                    'authorized',
                                    'rejected',
                                    'return_received',
                                    'completed',
                                    'failed',
                                    'recoverable_failure'
                                )
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
                        ENUM('pending', 'authorized', 'return_received', 'completed', 'failed', 'recoverable_failure')
                        NOT NULL DEFAULT 'pending';
SQL
        );
    }
}

<?php

use Illuminate\Database\Migrations\Migration;

class SimplifyStatusesOnEcommerceOrderRefunds extends Migration
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
                                ENUM('pending', 'denied', 'approved', 'processing', 'completed', 'failed')
                                NOT NULL DEFAULT 'pending';
SQL
        );

        DB::statement('ALTER TABLE `ecommerce_order_refunds` ADD INDEX `ecommerce_order_refunds_status` (`status`)');
        DB::statement('ALTER TABLE `ecommerce_order_refunds` ADD INDEX `ecommerce_order_refunds_status_order_id` (`status`, `order_id`)');

        DB::statement(<<<SQL
                ALTER TABLE `ecommerce_order_refunds` CHANGE `recoverable_failure_stage` `recoverable_failure_stage`
                    VARCHAR(50)
                    NULL
                    COMMENT 'used to be able recuperating from those errors after some successfully done remote process'
                    AFTER `status`;
SQL
        );

        DB::statement('ALTER TABLE `ecommerce_order_refunds` ADD INDEX `ecommerce_order_refunds_recoverable_failure_stage` (`recoverable_failure_stage`)');
        DB::statement('ALTER TABLE `ecommerce_order_refunds` ADD INDEX `ecommerce_order_refunds_recoverable_failure_stage_order_id` (`recoverable_failure_stage`, `order_id`)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `ecommerce_order_refunds` DROP INDEX `ecommerce_order_refunds_status`');
        DB::statement('ALTER TABLE `ecommerce_order_refunds` DROP INDEX `ecommerce_order_refunds_status_order_id`');
        DB::statement('ALTER TABLE `ecommerce_order_refunds` DROP INDEX `ecommerce_order_refunds_recoverable_failure_stage`');
        DB::statement('ALTER TABLE `ecommerce_order_refunds` DROP INDEX `ecommerce_order_refunds_recoverable_failure_stage_order_id`');

        DB::statement(<<<SQL
                        ALTER TABLE `ecommerce_order_refunds` CHANGE `status` `status`
                                ENUM('pending', 'authorized','return_received', 'completed', 'failed')
                                NOT NULL DEFAULT 'pending';
SQL
        );

        DB::statement(<<<SQL
               ALTER TABLE `ecommerce_order_refunds` CHANGE `recoverable_failure_stage` `recoverable_failure_stage`
                    ENUM('payment_gateway', 'textrail')
                    NULL
                    COMMENT 'used to be able recuperating from those errors after some successfully done remote process'
                    AFTER `status`;
SQL
        );
    }
}

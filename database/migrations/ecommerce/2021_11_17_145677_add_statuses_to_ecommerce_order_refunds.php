<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusesToEcommerceOrderRefunds extends Migration
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
                                ENUM('pending', 'authorized','return_received', 'completed', 'failed')
                                NOT NULL DEFAULT 'pending';
SQL
        );

        DB::statement(<<<SQL
                ALTER TABLE `ecommerce_order_refunds` ADD `recoverable_failure_stage`
                    ENUM('payment_gateway', 'textrail')
                    NULL
                    COMMENT 'used to be able recuperating from those errors after some successfully done remote process'
                    AFTER `status`;
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
                        ALTER TABLE `ecommerce_order_refunds`
                            CHANGE `status` `status`
                            ENUM('finished', 'failed', 'recoverable_failure')
                            NOT NULL DEFAULT 'finished';
SQL
        );

        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->dropColumn('recoverable_failure_stage');
        });
    }
}

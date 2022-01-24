<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefundSummaryToOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->decimal('refunded_amount', 10, 2)
                ->nullable()
                ->unsigned()
                ->after('refund_status');

            $table->text('refunded_parts')
                ->nullable()
                ->after('refunded_amount')
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
            $table->dropColumn('refunded_amount');
            $table->dropColumn('refunded_parts');
        });
    }
}

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Ecommerce\CompletedOrder\CompletedOrder;

class AddRefundStatusToOrderTable extends Migration
{
    private const REFUND_STATUSES = [
        CompletedOrder::REFUND_STATUS_UNREFUNDED,
        CompletedOrder::REFUND_STATUS_REFUNDED,
        CompletedOrder::REFUND_STATUS_PARTIAL_REFUNDED,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->enum('refund_status', self::REFUND_STATUSES)
                ->after('payment_intent')
                ->default(CompletedOrder::REFUND_STATUS_UNREFUNDED)
                ->index();
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
            $table->dropColumn('refund_status');
        });
    }
}

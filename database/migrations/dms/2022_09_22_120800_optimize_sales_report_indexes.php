<?php

use App\Models\CRM\Account\Invoice;
use App\Models\CRM\Account\Payment;
use App\Models\CRM\Dms\Payment\DealerRefund;
use App\Models\CRM\Dms\Payment\DealerSalesReceipt;
use App\Models\CRM\Dms\Quickbooks\Item;
use App\Models\CRM\Dms\Quickbooks\PaymentMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeSalesReportIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(DealerSalesReceipt::getTableName(), function (Blueprint $table) {
            $table->index(['dealer_id']);
            $table->index(['tb_name', 'tb_primary_id']);
        });

        Schema::table(DealerRefund::getTableName(), function (Blueprint $table) {
            $table->index(['dealer_id']);
            $table->index(['tb_name', 'tb_primary_id']);
        });

        Schema::table(Payment::getTableName(), function (Blueprint $table) {
            $table->index(['payment_method_id']);
            $table->index(['register_id']);
            $table->index(['dealer_id']);
            $table->index(['dealer_id', 'created_at']);
        });

        Schema::table(Item::getTableName(), function (Blueprint $table) {
            $table->index(['type']);
        });

        Schema::table(Invoice::getTableName(), function (Blueprint $table) {
            $table->index(['customer_id']);
        });

        Schema::table(PaymentMethod::getTableName(), function (Blueprint $table) {
            $table->index(['dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(DealerSalesReceipt::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['dealer_id']);
            $table->dropIndex(['tb_name', 'tb_primary_id']);
        });

        Schema::table(DealerRefund::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['dealer_id']);
            $table->dropIndex(['tb_name', 'tb_primary_id']);
        });

        Schema::table(Payment::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['payment_method_id']);
            $table->dropIndex(['register_id']);
            $table->dropIndex(['dealer_id']);
            $table->dropIndex(['dealer_id', 'created_at']);
        });

        Schema::table(Item::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['type']);
        });

        Schema::table(Invoice::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['customer_id']);
        });

        Schema::table(PaymentMethod::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['dealer_id']);
        });
    }
}

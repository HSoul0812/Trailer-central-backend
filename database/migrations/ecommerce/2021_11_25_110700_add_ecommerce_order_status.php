<?php

use App\Models\Ecommerce\CompletedOrder\CompletedOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEcommerceOrderStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->enum('ecommerce_order_status', CompletedOrder::ECOMMERCE_ORDER_STATUSES)->default('not_approved')->index();
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
            $table->dropColumn('ecommerce_order_status');
        });
    }
}

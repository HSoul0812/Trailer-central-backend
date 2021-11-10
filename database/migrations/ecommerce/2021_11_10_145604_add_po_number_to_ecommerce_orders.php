<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPoNumberToEcommerceOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->string('po_number', 20)
                ->after('dealer_id')
                ->nullable()
                ->unique();
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
            $table->dropColumn('po_number');
        });
    }
}

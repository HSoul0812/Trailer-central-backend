<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerIdToEcommerceSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->integer('dealer_id')->index()->after('id');
        });

        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->integer('dealer_id')->index()->after('id');
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
            $table->dropColumn('dealer_id');
        });

        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->dropColumn('dealer_id');
        });
    }
}

<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->decimal('tax', 10, 2)
                ->nullable();

            $table->decimal('total_before_tax', 10, 2)
                ->nullable();

            $table->decimal('tax_rate', 10, 2)
                ->nullable();

            $table->decimal('handling_fee', 10, 2)
                ->nullable();

            $table->decimal('shipping_fee', 10, 2)
                ->nullable();

            $table->decimal('subtotal', 10, 2)
                ->nullable();

            $table->decimal('in_store_pickup', 10, 2)
                ->nullable();
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
            $table->dropColumn('tax');
            $table->dropColumn('total_before_tax');
            $table->dropColumn('tax_rate');
            $table->dropColumn('handling_fee');
            $table->dropColumn('shipping_fee');
            $table->dropColumn('subtotal');
            $table->dropColumn('in_store_pickup');
        });
    }
}

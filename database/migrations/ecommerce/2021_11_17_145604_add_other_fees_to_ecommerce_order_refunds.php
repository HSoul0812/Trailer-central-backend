<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOtherFeesToEcommerceOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->integer('textrail_rma')
                ->after('reason')
                ->unsigned()
                ->nullable()
                ->unique()
                ->comment('TexTrail unique RMA id');

            $table->decimal('parts_amount', 10)
                ->unsigned()
                ->after('total_amount')
                ->default(0)
                ->comment('total refunded amount for parts');

            $table->decimal('shipping_amount', 10)
                ->unsigned()
                ->after('parts_amount')
                ->default(0);

            $table->decimal('handling_amount', 10)
                ->unsigned()
                ->after('shipping_amount')
                ->default(0);

            $table->decimal('adjustment_amount', 10)
                ->unsigned()
                ->after('handling_amount')
                ->default(0)
                ->comment('custom refund amount');

            $table->decimal('tax_amount', 10)
                ->unsigned()
                ->after('adjustment_amount')
                ->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->dropColumn([
                'textrail_rma',
                'parts_amount',
                'shipping_amount',
                'handling_amount',
                'adjustment_amount',
                'tax_amount'
            ]);
        });
    }
}

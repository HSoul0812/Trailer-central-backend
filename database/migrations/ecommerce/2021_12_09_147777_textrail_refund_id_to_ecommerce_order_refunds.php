<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TextrailRefundIdToEcommerceOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_order_refunds', static function (Blueprint $table) {
            $table->integer('textrail_refund_id')
                ->after('textrail_rma')
                ->unsigned()
                ->nullable()
                ->unique()
                ->comment('TexTrail unique refund id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('ecommerce_order_refunds', static function (Blueprint $table) {
            $table->dropColumn('textrail_refund_id');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedAtToEcommerceOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('failed_at')->nullable();
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
            $table->dropColumn(['updated_at', 'failed_at']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErrorRecoveringMechanismToEcommerceOrderRefunds extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_order_refunds', function (Blueprint $table) {
            $table->text('errors')
                ->after('metadata')
                ->nullable()
                ->comment('JSON encoded array of errors');
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
            $table->dropColumn('errors');
        });
    }
}

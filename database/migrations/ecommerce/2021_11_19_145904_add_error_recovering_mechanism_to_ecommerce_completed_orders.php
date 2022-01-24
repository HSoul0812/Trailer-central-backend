<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddErrorRecoveringMechanismToEcommerceCompletedOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ecommerce_completed_orders CHANGE created_at created_at TIMESTAMP NOT NULL AFTER phone_number;");
        DB::statement("ALTER TABLE ecommerce_completed_orders CHANGE updated_at updated_at TIMESTAMP NULL AFTER created_at;");

        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->text('errors')
                ->after('invoice_pdf_url')
                ->nullable()
                ->comment('JSON encoded array of errors');

            $table->timestamp('refunded_at')->after('updated_at')->nullable();
            $table->timestamp('failed_at')->after('refunded_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ecommerce_completed_orders CHANGE created_at created_at TIMESTAMP NOT NULL AFTER parts;");
        DB::statement("ALTER TABLE ecommerce_completed_orders CHANGE updated_at updated_at TIMESTAMP NULL AFTER created_at;");

        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->dropColumn([
                'errors',
                'refunded_at',
                'failed_at'
            ]);
        });
    }
}

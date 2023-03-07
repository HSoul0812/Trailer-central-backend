<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddUpdateAtToPaymentCalculator extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('website_payment_calculator_settings', static function (Blueprint $table) {
            $table->timestamp('updated_at')->useCurrent()->index('website_payment_calculator_settings_updated_at_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('website_payment_calculator_settings', static function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
}

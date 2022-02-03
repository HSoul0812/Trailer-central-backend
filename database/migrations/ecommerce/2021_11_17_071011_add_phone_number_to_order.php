<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhoneNumberToOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('ecommerce_completed_orders', function (Blueprint $table) {
            $table->string('phone_number', 15)
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
            $table->dropColumn('phone_number');
        });
    }
}

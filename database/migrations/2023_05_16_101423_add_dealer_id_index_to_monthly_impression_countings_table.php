<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDealerIdIndexToMonthlyImpressionCountingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('monthly_impression_countings', function (Blueprint $table) {
            $table->index(['dealer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_impression_countings', function (Blueprint $table) {
            $table->dropIndex(['dealer_id']);
        });
    }
}

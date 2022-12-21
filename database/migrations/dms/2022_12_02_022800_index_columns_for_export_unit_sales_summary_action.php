<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexColumnsForExportUnitSalesSummaryAction extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_category', function (Blueprint $table) {
            $table->index(['category']);
            $table->index(['legacy_category']);
        });

        Schema::table('inventory_floor_plan_payment', function (Blueprint $table) {
            $table->index(['inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_category', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['legacy_category']);
        });

        Schema::table('inventory_floor_plan_payment', function (Blueprint $table) {
            $table->dropIndex(['inventory_id']);
        });
    }
}

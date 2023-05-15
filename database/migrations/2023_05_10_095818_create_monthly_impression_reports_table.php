<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonthlyImpressionReportsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('monthly_impression_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->integer('dealer_id');
            $table->integer('inventory_id');
            $table->string('inventory_title')->nullable();
            $table->string('inventory_type')->nullable();
            $table->string('inventory_category')->nullable();
            $table->integer('plp_total_count')->comment('Total count of visit to the PLP pages')->default(0);
            $table->integer('pdp_total_count')->comment('Total count of visit to the PDP pages')->default(0);
            $table->integer('tt_dealer_page_total_count')->comment('Total count of visit to TT dealer page')->default(0);
            $table->timestamps();

            $table->index(['year', 'month']);
            $table->index(['year', 'month', 'dealer_id']);
            $table->unique(['year', 'month', 'inventory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('monthly_impression_reports');
    }
}

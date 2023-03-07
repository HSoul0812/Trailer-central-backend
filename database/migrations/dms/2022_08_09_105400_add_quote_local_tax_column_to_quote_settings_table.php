<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuoteLocalTaxColumnToQuoteSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_quote_settings', function (Blueprint $table) {
            $table->boolean('local_calculation_enabled')->after('include_fees_for_sales_tax')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_quote_settings', function (Blueprint $table) {
            $table->dropColumn('local_calculation_enabled');
        });
    }
}

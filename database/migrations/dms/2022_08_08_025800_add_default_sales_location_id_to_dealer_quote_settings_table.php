<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultSalesLocationIdToDealerQuoteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_quote_settings', function (Blueprint $table) {
            $table->unsignedBigInteger('default_sales_location_id')
                ->nullable()
                ->comment('The default sales location id, referencing to the dealer_location_id column on the dealer_location table.');
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
            $table->dropColumn('default_sales_location_id');
        });
    }
}

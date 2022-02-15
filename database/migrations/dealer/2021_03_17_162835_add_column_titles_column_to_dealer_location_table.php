<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTitlesColumnToDealerLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer_location', function (Blueprint $table) {
            $table->text('sales_tax_item_column_titles')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer_location', function (Blueprint $table) {
            $table->dropColumn('sales_tax_item_column_titles');
        });
    }
}

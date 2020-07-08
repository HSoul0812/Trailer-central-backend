<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ColumnUnitSaleFeeMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_fee', function (Blueprint $table) {
            $table->decimal('county_fee', 10, 2);
            $table->decimal('transfer_fee', 10, 2);
            $table->decimal('title_registration_fee', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('dms_unit_sale_fee', function (Blueprint $table) {
            $table->dropColumn('county_fee');
            $table->dropColumn('transfer_fee');
            $table->dropColumn('title_registration_fee');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUnitSaleFeeMissingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_fee', function (Blueprint $table) {
            $table->decimal('loan_fee', 10, 2);
            $table->decimal('dmv_fee', 10, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale_fee', function (Blueprint $table) {
            $table->dropColumn('loan_fee');
            $table->dropColumn('dmv_fee');
        });
    }
}

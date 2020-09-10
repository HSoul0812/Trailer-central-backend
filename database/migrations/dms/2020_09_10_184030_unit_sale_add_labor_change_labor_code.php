<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UnitSaleAddLaborChangeLaborCode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_unit_sale_labor', function (Blueprint $table) {
            //
            DB::statement('ALTER TABLE `dms_unit_sale_labor` CHANGE labor_code labor_code INT UNSIGNED NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_unit_sale_labor', function (Blueprint $table) {
            //
            $table->string('labor_code')->change();
        });
    }
}

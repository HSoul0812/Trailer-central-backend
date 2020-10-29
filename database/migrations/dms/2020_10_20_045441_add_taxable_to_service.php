<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxableToService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_service_technician', function (Blueprint $table) {
            $table->tinyInteger('taxable')->default(1);
        });
        Schema::table('dms_part_item', function (Blueprint $table) {
            $table->tinyInteger('taxable')->default(1);
        });
        Schema::table('dms_repair_misc_part_item', function (Blueprint $table) {
            $table->tinyInteger('taxable')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_service_technician', function (Blueprint $table) {
            $table->dropColumn('taxable');
        });
        Schema::table('dms_part_item', function (Blueprint $table) {
            $table->dropColumn('taxable');
        });
        Schema::table('dms_repair_misc_part_item', function (Blueprint $table) {
            $table->dropColumn('taxable');
        });
    }
}

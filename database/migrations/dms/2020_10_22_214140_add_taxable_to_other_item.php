<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxableToOtherItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dms_other_item', function (Blueprint $table) {
            $table->tinyInteger('taxable')->default(1);
        });
        Schema::table('dms_service_item', function (Blueprint $table) {
            $table->tinyInteger('taxable')->default(1);
        });
        Schema::table('dms_service_technician', function (Blueprint $table) {
            $table->dropColumn('taxable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_other_item', function (Blueprint $table) {
            $table->dropColumn('taxable');
        });
        Schema::table('dms_service_item', function (Blueprint $table) {
            $table->dropColumn('taxable');
        });
        Schema::table('dms_service_technician', function (Blueprint $table) {
            $table->tinyInteger('taxable')->default(1);
        });
    }
}

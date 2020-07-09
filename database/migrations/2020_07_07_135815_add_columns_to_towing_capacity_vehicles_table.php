<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTowingCapacityVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('towing_capacity_vehicles', function (Blueprint $table) {
            $table->string('tow_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('gear_ratio')->nullable();
            $table->boolean('towing_package_required')->default(0);
            $table->boolean('payload_package_required')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('towing_capacity_vehicles', function (Blueprint $table) {
            $table->dropColumn('tow_type');
            $table->dropColumn('transmission');
            $table->dropColumn('gear_ratio');
            $table->dropColumn('towing_package_required');
            $table->dropColumn('payload_package_required');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleTowingCapacityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('towing_capacity_vehicles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->year('year');
            $table->unsignedInteger('make_id');
            $table->string('model');
            $table->string('sub_model');
            $table->string('drive_train');
            $table->string('engine');
            $table->integer('tow_limit');
        });

        Schema::table('towing_capacity_vehicles', function (Blueprint $table) {
            $table->foreign('make_id')->references('id')->on('towing_capacity_makes');

            $table->index('year');
            $table->index(['year', 'make_id'], 'towing_capacity_vehicles_year_make_index');
            $table->index(['year', 'make_id', 'model'], 'towing_capacity_vehicles_year_make_model_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('towing_capacity_vehicles');
    }
}

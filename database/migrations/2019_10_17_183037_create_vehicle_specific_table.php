<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleSpecificTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('vehicle_specific')) {
            Schema::create('vehicle_specific', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('make');
                $table->string('model');
                $table->smallInteger('year_from');
                $table->smallInteger('year_to');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_specific');
    }
}

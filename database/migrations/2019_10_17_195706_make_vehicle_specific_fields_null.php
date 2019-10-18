<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeVehicleSpecificFieldsNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_specific', function (Blueprint $table) {                 
  
            $table->string('make')->nullable()->change();
            $table->string('model')->nullable()->change();
            $table->smallInteger('year_from')->nullable()->change();
            $table->smallInteger('year_to')->nullable()->change();
    
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
    }
}

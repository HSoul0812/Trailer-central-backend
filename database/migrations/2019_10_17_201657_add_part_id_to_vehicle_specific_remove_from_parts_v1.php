<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartIdToVehicleSpecificRemoveFromPartsV1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vehicle_specific', function (Blueprint $table) {            
            $table->bigInteger('part_id')->unsigned();
            $table->foreign('part_id')
                    ->references('id')
                    ->on('parts_v1')
                    ->onDelete('CASCADE')
                    ->onUpdate('CASCADE');             
            
        });
        
        Schema::table('parts_v1', function (Blueprint $table) {
            $table->dropForeign('parts_v1_vehicle_specific_id_foreign');
            $table->dropColumn('vehicle_specific_id');         
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vehicle_specific', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVehicleSpecificIdToParts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $pdo = DB::connection()->getPdo();
        $pdo->query("ALTER TABLE parts_v1 MODIFY COLUMN vehicle_specific_id bigint(20) UNSIGNED;");
        
        Schema::table('parts_v1', function (Blueprint $table) {  
            $table->foreign('vehicle_specific_id')
                    ->references('id')
                    ->on('vehicle_specific')
                    ->onDelete('SET NULL')
                    ->onUpdate('CASCADE'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parts_v1', function (Blueprint $table) {
            //
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixChangeAmcmarinesalesName extends Migration
{
    private const MARINE_WORLD_INTEGRATION_ID = 78;

    private const AMCMARINESALES_PARAMS = [
        'code' => 'boatcrazy',
        'module_name' => 'boatcrazy',
        'name' => 'Boat Crazy',

    ]; 
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::table('integration')
        ->where('integration_id', self::MARINE_WORLD_INTEGRATION_ID)
        ->update(self::AMCMARINESALES_PARAMS);
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

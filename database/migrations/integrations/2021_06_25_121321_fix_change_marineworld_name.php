<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixChangeMarineworldName extends Migration
{

  private const MARINE_WORLD_INTEGRATION_ID = 75;
  
  private const MARINEWORLD_PARAM_VALUE = 'OnlyInboards';


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      DB::table('integration')
        ->where('integration_id', self::MARINE_WORLD_INTEGRATION_ID)
        ->update(['name' => self::MARINEWORLD_PARAM_VALUE]);
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

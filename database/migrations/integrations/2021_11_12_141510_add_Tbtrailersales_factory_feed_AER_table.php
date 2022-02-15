<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTbtrailersalesFactoryFeedAERTable extends Migration
{
    private const DEALER_PARAMS_ENTITY = [
        'entity_id' => 41383,
        'reference_id' => '7535',
        'entity_type' => 'dealer',
        'api_key' => 'lamar'
    ];

    private const DEALER_PARAMS_LOCATION = [
        'entity_id' => 12073,
        'reference_id' => '7535',
        'entity_type' => 'dealer_location',
        'api_key' => 'lamar'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('api_entity_reference')->insert(self::DEALER_PARAMS_ENTITY);
        DB::table('api_entity_reference')->insert(self::DEALER_PARAMS_LOCATION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('api_entity_reference')->where(self::DEALER_PARAMS_LOCATION)->delete();
        DB::table('api_entity_reference')->where(self::DEALER_PARAMS_ENTITY)->delete();
    }
}

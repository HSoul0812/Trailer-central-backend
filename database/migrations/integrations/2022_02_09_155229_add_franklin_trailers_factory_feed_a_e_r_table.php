<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFranklinTrailersFactoryFeedAERTable extends Migration
{
    private const DEALER_PARAMS_ENTITY = [
        'entity_id' => 5780,
        'reference_id' => 'fratra',
        'entity_type' => 'dealer',
        'api_key' => 'pj'
    ];

    private const DEALER_PARAMS_LOCATION = [
        'entity_id' => 9100,
        'reference_id' => 'fratra',
        'entity_type' => 'dealer_location',
        'api_key' => 'pj'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() : void
    {
        DB::table('api_entity_reference')->insert(self::DEALER_PARAMS_ENTITY);
        DB::table('api_entity_reference')->insert(self::DEALER_PARAMS_LOCATION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() : void
    {
        DB::table('api_entity_reference')->where(self::DEALER_PARAMS_LOCATION)->delete();
        DB::table('api_entity_reference')->where(self::DEALER_PARAMS_ENTITY)->delete();
    }
}

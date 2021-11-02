<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGooddealusedcarsllcFactoryFeedAERTable extends Migration
{
    private const DEALER_PARAMS_ENTITY = [
        'entity_id' => 1486,
        'reference_id' => '9591',
        'entity_type' => 'dealer',
        'api_key' => 'lgs'
    ];

    private const DEALER_PARAMS_LOCATION = [
        'entity_id' => 19853,
        'reference_id' => '9591',
        'entity_type' => 'dealer_location',
        'api_key' => 'lgs'
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
        DB::table('integrations')->where(self::DEALER_PARAMS_LOCATION)->delete();
        DB::table('integrations')->where(self::DEALER_PARAMS_ENTITY)->delete();
    }
}

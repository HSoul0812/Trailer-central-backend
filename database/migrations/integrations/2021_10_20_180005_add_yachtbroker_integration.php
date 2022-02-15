<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddYachtbrokerIntegration extends Migration
{

    private const YACHTBROKER_PARAMS = [
        'integration_id' => 81,
        'code' => 'yachtbroker',
        'module_name' => 'yachtbroker',
        'module_status' => 'idle',
        'name' => 'YachtBroker',
        'description' => null,
        'domain' => '',
        'create_account_url' => '',
        'active' => 1,
        'filters' => 'a:1:{i:0;a:2:{s:6:"filter";a:1:{i:0;a:3:{s:5:"field";s:9:"condition";s:5:"value";s:4:"used";s:8:"operator";s:2:"or";}}s:8:"operator";s:3:"and";}}',
        'frequency' => 7200,
        'settings' => 'a:0:{}',
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    private const YACHTBROKER_DEALER = [
        'integration_id' => 81,
        'dealer_id' => 9549,
        'active' => 1,
        'settings' => [],
        'location_ids' => '',
        'msg_title' => '',
        'msg_body' => '',
        'msg_date' => '0000-00-00 00:00:00',
        'include_pending_sale' => 0
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::transaction(function ()
        {
            DB::table('integration')->insert(self::YACHTBROKER_PARAMS);

            $yachtbrokerDealer = self::YACHTBROKER_DEALER;
            $yachtbrokerDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $yachtbrokerDealer['settings'] = serialize($yachtbrokerDealer['settings']);

            DB::table('integration_dealer')->insert($yachtbrokerDealer);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function ()
        {
            DB::table('integration_dealer')->delete(self::YACHTBROKER_PARAMS['integration_id']);
            DB::table('integration')->delete(self::YACHTBROKER_PARAMS['integration_id']);
        });
    }
}

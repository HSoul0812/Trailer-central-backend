<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddGolfCartResourceIntegration extends Migration
{

    private const GOLFCARTRESOURCE_PARAMS = [
        'integration_id' => 80,
        'code' => 'golfcartresource',
        'module_name' => 'golfcartresource',
        'module_status' => 'idle',
        'name' => 'GolfCartResource',
        'description' => null,
        'domain' => '',
        'create_account_url' => '',
        'active' => 1,
        'filters' => 'a:1:{i:0;a:2:{s:6:"filter";a:1:{i:0;a:3:{s:5:"field";s:8:"category";s:5:"value";s:9:"golf_cart";s:8:"operator";s:2:"or";}}s:8:"operator";s:3:"and";}}',
        'frequency' => 21600,
        'settings' => 'a:0:{}',
        'include_sold' => 0,
        'uses_staging' => 0,
        'show_for_integrated' => 0
    ];

    private const GOLFCARTRESOURCE_DEALER = [
        'integration_id' => 80,
        'dealer_id' => 8506,
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
            DB::table('integration')->insert(self::GOLFCARTRESOURCE_PARAMS);

            $pushnpullDealer = self::GOLFCARTRESOURCE_DEALER;
            $pushnpullDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $pushnpullDealer['settings'] = serialize($pushnpullDealer['settings']);

            DB::table('integration_dealer')->insert($pushnpullDealer);
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
            DB::table('integration_dealer')->delete(self::GOLFCARTRESOURCE_PARAMS['integration_id']);
            DB::table('integration')->delete(self::GOLFCARTRESOURCE_PARAMS['integration_id']);
        });
    }
}

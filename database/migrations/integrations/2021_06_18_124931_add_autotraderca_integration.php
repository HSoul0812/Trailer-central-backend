<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAutoTraderCAIntegration extends Migration
{
    private const AUTOTRADER_PARAMS = [
        'integration_id' => 74,
        'code' => 'autotraderca',
        'module_name' => 'autotraderca',
        'module_status' => 'idle',
        'name' => 'AutoTraderCA',
        'description' => null,
        'domain' => '',
        'create_account_url' => '',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => 'a:0:{}',
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ]; 

    private const AUTOTRADER_DEALER = [
        'integration_id' => 74,
        'dealer_id' => 1001,
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
        DB::table('integration')->insert(self::AUTOTRADER_PARAMS);

        $autoTraderDealer = self::AUTOTRADER_DEALER;
        $autoTraderDealer['created_at'] = (new \DateTime())->format('Y:m:d H:i:s');
        $autoTraderDealer['settings'] = serialize($autoTraderDealer['settings']);

        DB::table('integration_dealer')->insert($autoTraderDealer);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration_dealer')->delete(self::AUTOTRADER_PARAMS['integration_id']);
        DB::table('integration')->delete(self::AUTOTRADER_PARAMS['integration_id']);
    }
}

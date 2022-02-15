<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddHammertimesIntegration extends Migration
{
    private const HAMMER_PARAMS = [
        'integration_id' => 72,
        'code' => 'hammertimes',
        'module_name' => 'hammertimes',
        'module_status' => 'idle',
        'name' => 'HammerTimes',
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

    private const HAMMER_DEALER = [
        'integration_id' => 72,
        'dealer_id' => 9513,
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
        DB::table('integration')->insert(self::HAMMER_PARAMS);

        $v12Dealer = self::HAMMER_DEALER;
        $v12Dealer['created_at'] = (new \DateTime())->format('Y:m:d H:i:s');
        $v12Dealer['settings'] = serialize($v12Dealer['settings']);

        DB::table('integration_dealer')->insert($v12Dealer);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration_dealer')->delete(self::HAMMER_PARAMS['integration_id']);
        DB::table('integration')->delete(self::HAMMER_PARAMS['integration_id']);
    }
}

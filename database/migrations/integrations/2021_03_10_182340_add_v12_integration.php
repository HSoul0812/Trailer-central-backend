<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddV12Integration extends Migration
{
    private const V12_PARAMS = [
        'integration_id' => 71,
        'code' => 'v12',
        'module_name' => 'V12',
        'module_status' => 'idle',
        'name' => 'V12',
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

    private const V12_DEALER = [
        'integration_id' => 71,
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
        DB::table('integration')->insert(self::V12_PARAMS);

        $v12Dealer = self::V12_DEALER;
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
        DB::table('integration_dealer')->delete(self::V12_PARAMS['integration_id']);
        DB::table('integrations')->delete(self::V12_PARAMS['integration_id']);
    }
}

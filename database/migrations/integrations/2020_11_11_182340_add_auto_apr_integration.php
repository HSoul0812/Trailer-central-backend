<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddAutoAprIntegration extends Migration
{
    private const AUTO_APR_PARAMS = [
        'integration_id' => 66,
        'code' => 'autoapr',
        'module_name' => 'AutoAPR',
        'module_status' => 'idle',
        'name' => 'AutoAPR',
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

    private const AUTO_APR_DEALER = [
        'integration_id' => 66,
        'dealer_id' => 9268,
        'active' => 1,
        'settings' => ['dealer_id' => '88MJ5VV4'],
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
        DB::table('integration')->insert(self::AUTO_APR_PARAMS);

        $autoAprDealer = self::AUTO_APR_DEALER;
        $autoAprDealer['created_at'] = (new \DateTime())->format('Y:m:d H:i:s');
        $autoAprDealer['settings'] = serialize($autoAprDealer['settings']);

        DB::table('integration_dealer')->insert($autoAprDealer);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration_dealer')->delete(self::AUTO_APR_PARAMS['integration_id']);
        DB::table('integrations')->delete(self::AUTO_APR_PARAMS['integration_id']);
    }
}

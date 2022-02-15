<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddBoatlineIntegration extends Migration
{ 
    private const GENERIC_CLIENT_PARAMS = [
        'integration_id' => 70,
        'code' => 'boatline',
        'module_name' => 'BoatLine',
        'module_status' => 'idle',
        'name' => 'BoatLine',
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

    private const GENERIC_CLIENT_DEALER = [
        'integration_id' => 70,
        'dealer_id' => 1001,
        'active' => 1,
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
        DB::table('integration')->insert(self::GENERIC_CLIENT_PARAMS);

        $genericCleintDealer = self::GENERIC_CLIENT_DEALER;
        $genericCleintDealer['created_at'] = (new \DateTime())->format('Y:m:d H:i:s');

        DB::table('integration_dealer')->insert($genericCleintDealer);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration_dealer')->delete(self::GENERIC_CLIENT_PARAMS['integration_id']);
        DB::table('integrations')->delete(self::GENERIC_CLIENT_PARAMS['integration_id']);
    }
}

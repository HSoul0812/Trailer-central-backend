<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDealervisionAlternateToIntegrationTable extends Migration
{
    private const INTEGRATION_IDS = [98, 99, 100, 101, 102];

    private const TABLE_NAME = 'integration';

    private const INTEGRATION_DATA = [
        'code' => 'dealervision',
        'module_name' => 'dealervision',
        'module_status' => 'idle',
        'name' => 'DealerVision Alternate',
        'description' => null,
        'domain' => '',
        'create_account_url' => '',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => 'a:4:{i:0;a:5:{s:4:"name";s:9:"dealer_id";s:5:"label";s:9:"Dealer ID";s:11:"description";s:34:"Your DealerVision match dealer ID.";s:4:"type";s:4:"text";s:8:"required";b:1;}i:1;a:5:{s:4:"name";s:4:"host";s:5:"label";s:4:"Host";s:11:"description";s:20:"Ip or domain for ftp";s:4:"type";s:4:"text";s:8:"required";b:0;}i:2;a:5:{s:4:"name";s:8:"username";s:5:"label";s:8:"Username";s:11:"description";s:31:"Username for the ftp connection";s:4:"type";s:4:"text";s:8:"required";b:0;}i:3;a:5:{s:4:"name";s:8:"password";s:5:"label";s:8:"Password";s:11:"description";s:31:"Password for the ftp connection";s:4:"type";s:4:"text";s:8:"required";b:0;}}',
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 0; $i < count(self::INTEGRATION_IDS); $i++) {
            $integrationData = self::INTEGRATION_DATA;
            $number = $i + 1;

            $integrationData['integration_id'] = self::INTEGRATION_IDS[$i];
            $integrationData['code'] = $integrationData['code'] . $number;
            $integrationData['module_name'] = $integrationData['module_name'] . $number;
            $integrationData['name'] = $integrationData['name'] . " $number";

            DB::table(self::TABLE_NAME)->insert($integrationData);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::INTEGRATION_IDS as $integrationId) {
            DB::table('integration')
                ->where('integration_id', $integrationId)
                ->delete();
        }
    }
}

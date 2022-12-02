<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDealervisionAlternateToIntegrationTable extends Migration
{
    private const INTEGRATION_IDS = [98, 99, 100, 101, 102];

    private const DELAERVISION_INTEGRATION_ID = 89;

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
        $dealervisionIntegration = DB::table(self::TABLE_NAME)
            ->where('integration_id', '=', self::DELAERVISION_INTEGRATION_ID)
            ->first();

        for ($i = 0; $i < count(self::INTEGRATION_IDS); $i++) {
            $integrationData = self::INTEGRATION_DATA;
            $number = $i + 1;

            $integrationData['integration_id'] = self::INTEGRATION_IDS[$i];
            $integrationData['code'] = $integrationData['code'] . $number;
            $integrationData['module_name'] = $integrationData['module_name'] . $number;
            $integrationData['name'] = $integrationData['name'] . " $number";
            $integrationData['settings'] = $dealervisionIntegration->settings;

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

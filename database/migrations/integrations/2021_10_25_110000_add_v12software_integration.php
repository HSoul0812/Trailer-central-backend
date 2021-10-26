<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddV12softwareIntegration extends Migration
{

    private const V12SOFTWARE_PARAMS = [
        'integration_id' => 82,
        'code' => 'v12software',
        'module_name' => 'v12software',
        'module_status' => 'idle',
        'name' => 'V12Software',
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

    private const V12SOFTWARE_DEALER = [
        'integration_id' => 82,
        'dealer_id' => 6989,
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
            DB::table('integration')->insert(self::V12SOFTWARE_PARAMS);

            $V12Dealer = self::V12SOFTWARE_DEALER;
            $V12Dealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $V12Dealer['settings'] = serialize($V12Dealer['settings']);

            DB::table('integration_dealer')->insert($V12Dealer);
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
            DB::table('integration_dealer')->delete(self::V12SOFTWARE_PARAMS['integration_id']);
            DB::table('integration')->delete(self::V12SOFTWARE_PARAMS['integration_id']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddCastanetAutomallIntegration extends Migration
{

    private const CASTANET_AUTOMALL_PARAMS = [
        'integration_id' => 84,
        'code' => 'castanetautomall',
        'module_name' => 'castanetautomall',
        'module_status' => 'idle',
        'name' => 'Castanet Automall',
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

    private const TRAVELAND_RV_DEALER = [
        'integration_id' => 84,
        'dealer_id' => 10590,
        'active' => 1,
        'settings' => [],
        'location_ids' => '',
        'msg_title' => '',
        'msg_body' => '',
        'msg_date' => '0000-00-00 00:00:00',
        'include_pending_sale' => 0
    ];

    private const AIRSTREAM_OF_TRAVELAND_DEALER = [
        'integration_id' => 84,
        'dealer_id' => 10591,
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
    public function up(): void
    {
        DB::transaction(function () {
            $travelandrvDealer = self::TRAVELAND_RV_DEALER;
            $travelandrvDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $travelandrvDealer['settings'] = serialize($travelandrvDealer['settings']);

            $airstreamoftravelandDealer = self::AIRSTREAM_OF_TRAVELAND_DEALER;
            $airstreamoftravelandDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $airstreamoftravelandDealer['settings'] = serialize($airstreamoftravelandDealer['settings']);

            DB::table('integration')->insert(self::CASTANET_AUTOMALL_PARAMS);
            DB::table('integration_dealer')->insert($travelandrvDealer);
            DB::table('integration_dealer')->insert($airstreamoftravelandDealer);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::table('integration_dealer')->delete(self::AIRSTREAM_OF_TRAVELAND_DEALER['integration_id']);
            DB::table('integration_dealer')->delete(self::TRAVELAND_RV_DEALER['integration_id']);
            DB::table('integration')->delete(self::CASTANET_AUTOMALL_PARAMS['integration_id']);
        });
    }
}

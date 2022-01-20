<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddCastanetAutomallIntegration extends Migration
{
    public const TRAVELAND_RV_DEALER_ID = 10590;
    public const AIRSTREAM_OF_TRAVELAND_DEALER_ID = 10591;

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
        'dealer_id' => self::TRAVELAND_RV_DEALER_ID,
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
        'dealer_id' => self::AIRSTREAM_OF_TRAVELAND_DEALER_ID,
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
        $checkIntegration = DB::table('integration')->where('integration_id', self::CASTANET_AUTOMALL_PARAMS['integration_id'])->exists();

        if (!$checkIntegration) {
            DB::transaction(function () {
                DB::table('integration')->insert(self::CASTANET_AUTOMALL_PARAMS);

                $checkAirstream = DB::table('dealer')->where('dealer_id', self::AIRSTREAM_OF_TRAVELAND_DEALER_ID)->exists();
                $checkTraveland = DB::table('dealer')->where('dealer_id', self::TRAVELAND_RV_DEALER_ID)->exists();

                if ($checkAirstream) {
                    $airstreamoftravelandDealer = self::AIRSTREAM_OF_TRAVELAND_DEALER;
                    $airstreamoftravelandDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
                    $airstreamoftravelandDealer['settings'] = serialize($airstreamoftravelandDealer['settings']);
                    DB::table('integration_dealer')->insert($airstreamoftravelandDealer);
                }

                if ($checkTraveland) {
                    $travelandrvDealer = self::TRAVELAND_RV_DEALER;
                    $travelandrvDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
                    $travelandrvDealer['settings'] = serialize($travelandrvDealer['settings']);
                    DB::table('integration_dealer')->insert($travelandrvDealer);
                }
            });
        }
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class AddCargurusIntegration extends Migration
{
    private const CARGURUS_ID = 100;
    private const ALL_SEASONS_POWERSPORTS_ID = 8755;

    private const CARGURUS_PARAMS = [
        'integration_id' => self::CARGURUS_ID,
        'code' => 'cargurus',
        'module_name' => 'cargurus',
        'module_status' => 'idle',
        'name' => 'CarGurus',
        'description' => "List all your Cars on CarGurus",
        'domain' => 'https://www.cargurus.com/',
        'create_account_url' => 'https://www.cargurus.com/Cars/dealer/signup',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => 'a:0:{}',
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    private const ALL_SEASONS_POWERSPORTS = [
        'integration_id' => self::CARGURUS_ID,
        'dealer_id' => self::ALL_SEASONS_POWERSPORTS_ID,
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
            DB::table('integration')->insert(self::CARGURUS_PARAMS);

            $dealer = self::ALL_SEASONS_POWERSPORTS;
            $dealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $dealer['settings'] = serialize($dealer['settings']);

            DB::table('integration_dealer')->insert($dealer);
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
            DB::table('integration_dealer')
                ->where('integration_id', self::CARGURUS_ID)
                ->delete();

            DB::table('integration')
                ->where('integration_id', self::CARGURUS_ID)
                ->delete();
        });
    }
}

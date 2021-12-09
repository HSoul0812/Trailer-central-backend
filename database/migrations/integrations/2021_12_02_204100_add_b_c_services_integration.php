<?php

use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddBCServicesIntegration extends Migration
{

    private const BC_SERVICES_PARAMS = [
        'integration_id' => 83,
        'code' => 'bcservices',
        'module_name' => 'bcservices',
        'module_status' => 'idle',
        'name' => 'B&C Services',
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

    private const BC_SERVICES_DEALER = [
        'integration_id' => 83,
        'dealer_id' => 4052,
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
            DB::table('integration')->insert(self::BC_SERVICES_PARAMS);

            $bcservicesDealer = self::BC_SERVICES_DEALER;
            $bcservicesDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
            $bcservicesDealer['settings'] = serialize($bcservicesDealer['settings']);

            DB::table('integration_dealer')->insert($bcservicesDealer);
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
            DB::table('integration_dealer')->delete(self::BC_SERVICES_PARAMS['integration_id']);
            DB::table('integration')->delete(self::BC_SERVICES_PARAMS['integration_id']);
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddRvasapIntegration extends Migration
{
  
  private const RVASAP_PARAMS = [
      'integration_id' => 79,
      'code' => 'rvasap',
      'module_name' => 'rvasap',
      'module_status' => 'idle',
      'name' => 'RVASAP',
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

  private const RVASAP_DEALER = [
      'integration_id' => 79,
      'dealer_id' => 9719,
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
      DB::transaction(function () {
        DB::table('integration')->insert(self::RVASAP_PARAMS);

        $rvasapDealer = self::RVASAP_DEALER;
        $rvasapDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
        $rvasapDealer['settings'] = serialize($rvasapDealer['settings']);

        DB::table('integration_dealer')->insert($rvasapDealer);
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::transaction(function () {
        DB::table('integration_dealer')->delete(self::RVASAP_PARAMS['integration_id']);
        DB::table('integration')->delete(self::RVASAP_PARAMS['integration_id']);
      });
    }
}

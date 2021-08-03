<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddLespacIntegration extends Migration
{
  
  private const LESPAC_PARAMS = [
      'integration_id' => 77,
      'code' => 'lespac',
      'module_name' => 'lespac',
      'module_status' => 'idle',
      'name' => 'LesPac',
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

  private const SUNTANRVMARINE_DEALER = [
      'integration_id' => 77,
      'dealer_id' => 9497,
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
        DB::table('integration')->insert(self::LESPAC_PARAMS);

        $marineWorldDealer = self::SUNTANRVMARINE_DEALER;
        $marineWorldDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
        $marineWorldDealer['settings'] = serialize($marineWorldDealer['settings']);

        DB::table('integration_dealer')->insert($marineWorldDealer);
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
        DB::table('integration_dealer')->delete(self::LESPAC_PARAMS['integration_id']);
        DB::table('integration')->delete(self::LESPAC_PARAMS['integration_id']);
      });
    }
}

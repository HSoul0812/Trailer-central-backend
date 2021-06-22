<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;

class AddMarineworldIntegration extends Migration
{

    private const MARINEWORLD_PARAMS = [
        'integration_id' => 75,
        'code' => 'marineworld',
        'module_name' => 'marineworld',
        'module_status' => 'idle',
        'name' => 'MarineWorld',
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

    private const MARINEWORLD_DEALER = [
        'integration_id' => 75,
        'dealer_id' => 9554,
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
      DB::table('integration')->insert(self::MARINEWORLD_PARAMS);

      $marineWorldDealer = self::MARINEWORLD_DEALER;
      $marineWorldDealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
      $marineWorldDealer['settings'] = serialize($marineWorldDealer['settings']);

      DB::table('integration_dealer')->insert($marineWorldDealer);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      DB::table('integration_dealer')->delete(self::MARINEWORLD_PARAMS['integration_id']);
      DB::table('integration')->delete(self::MARINEWORLD_PARAMS['integration_id']);
    }
}

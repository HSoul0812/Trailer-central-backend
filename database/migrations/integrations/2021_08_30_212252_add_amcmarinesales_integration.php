<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AddAmcmarinesalesIntegration extends Migration
{
  private const AMCMARINESALES_PARAMS = [
      'integration_id' => 78,
      'code' => 'amcmarinesales',
      'module_name' => 'amcmarinesales',
      'module_status' => 'idle',
      'name' => 'AMC Marine Sales & Services',
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

  private const AMCMARINESALES_DEALER = [
      'integration_id' => 78,
      'dealer_id' => 9558,
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
        DB::table('integration')->insert(self::AMCMARINESALES_PARAMS);

        $marineWorldDealer = self::AMCMARINESALES_DEALER;
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
        DB::table('integration_dealer')->delete(self::AMCMARINESALES_PARAMS['integration_id']);
        DB::table('integration')->delete(self::AMCMARINESALES_PARAMS['integration_id']);
      });
    }
}

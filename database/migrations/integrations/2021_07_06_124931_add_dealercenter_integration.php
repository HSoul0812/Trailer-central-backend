<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Traits\Migrations\Integrations\OutgoingIntegrationTrait;

class AddDealercenterIntegration extends Migration
{
    use OutgoingIntegrationTrait;
    
    private const DEALER_ID_SETTING_LABEL = 'Your Dealer Center ID';
    
    private const DEALERCENTER_PARAMS = [
        'integration_id' => 76,
        'code' => 'dealercenter',
        'module_name' => 'dealercenter',
        'module_status' => 'idle',
        'name' => 'DealerCenter',
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

    private const DEALERCENTER_DEALER = [
        'integration_id' => 76,
        'dealer_id' => 1001,
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
        DB::table('integration')->insert($this->getIntegrationParams());

        $dealerCenterDealer = self::DEALERCENTER_DEALER;
        $dealerCenterDealer['created_at'] = (new \DateTime())->format('Y:m:d H:i:s');
        $dealerCenterDealer['settings'] = serialize($dealerCenterDealer['settings']);

        DB::table('integration_dealer')->insert($dealerCenterDealer);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration_dealer')->delete(self::DEALERCENTER_PARAMS['integration_id']);
        DB::table('integration')->delete(self::DEALERCENTER_PARAMS['integration_id']);
    }
    
    private function getIntegrationParams() : array
    {
        $params = self::DEALERCENTER_PARAMS;
        $params['settings'] = $this->getDealerIdSettingsCode(self::DEALER_ID_SETTING_LABEL);
        return $params;
    }
}

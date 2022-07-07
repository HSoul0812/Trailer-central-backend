<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDealervisionIntegration extends Migration
{

    private const TABLE_NAME = 'integration';

    private const DEALER_INTEGRATION_TABLE_NAME = 'integration_dealer';

    private const INTEGRATION_PARAMS = [
        'integration_id' => 89,
        'code' => 'dealervision',
        'module_name' => 'dealervision',
        'module_status' => 'idle',
        'name' => 'DealerVision',
        'description' => null,
        'domain' => '',
        'create_account_url' => '',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => [],
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    private const INTEGRATION_DEALER_YOUNGBLOODS = [
        'integration_id' => 89,
        'dealer_id' => 11319,
        'active' => 1,
        'settings' => [],
        'location_ids' => '',
        'msg_title' => '',
        'msg_body' => '',
        'msg_date' => '0000-00-00 00:00:00',
        'include_pending_sale' => 0
    ];

    private const INTEGRATION_SETTINGS = [
        0 => [
            'name' => 'dealer_id',
            'label' => 'Dealer ID',
            'description' => 'Your DealerVision match dealer ID.',
            'type' => 'text',
            'required' => true,
        ],
        1 => [
            'name' => 'host',
            'label' => 'Host',
            'description' => 'Ip or domain for ftp',
            'type' => 'text',
            'required' => false,
        ],
        2 => [
            'name' => 'username',
            'label' => 'Username',
            'description' => 'Username for the ftp connection',
            'type' => 'text',
            'required' => false,
        ],
        3 => [
            'name' => 'password',
            'label' => 'Password',
            'description' => 'Password for the ftp connection',
            'type' => 'text',
            'required' => false,
        ],
    ];

    private const DEALER_SETTINGS = [
        'dealer_id' => '11319',
        'host' => 'ftp.dealervision.com',
        'username' => 'trailercentral',
        'password' => 'dvtcax93'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        if (!$this->checkIntegration()) {
            DB::transaction(static function () {
                $integration = self::INTEGRATION_PARAMS;
                $integration['settings'] = serialize(self::INTEGRATION_SETTINGS);
                DB::table(self::TABLE_NAME)->insert($integration);
            });
        }

        if ($this->checkIntegration() && !$this->checkIntegrationDealer()) {
            DB::transaction(static function () {
                $integration_dealer = self::INTEGRATION_DEALER_YOUNGBLOODS;
                $integration_dealer['created_at'] = (new \DateTime())->format('Y:m:d H:i:s');
                $integration_dealer['settings'] = serialize(self::DEALER_SETTINGS);
                DB::table(self::DEALER_INTEGRATION_TABLE_NAME)->insert($integration_dealer);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        DB::table('integration_dealer')->delete(self::INTEGRATION_PARAMS['integration_id']);
        DB::table('integration')->delete(self::INTEGRATION_PARAMS['integration_id']);
    }

    /**
     * @return bool
     */
    private function checkIntegration(): bool {
        $checkIntegration = DB::table(self::TABLE_NAME)->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])->exists();

        if ($checkIntegration) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkIntegrationDealer(): bool {
        $checkIntegrationDealer = DB::table(self::DEALER_INTEGRATION_TABLE_NAME)->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])->where('dealer_id', self::INTEGRATION_DEALER_YOUNGBLOODS['dealer_id'])->exists();

        if ($checkIntegrationDealer) {
            return true;
        }
        return false;
    }
}

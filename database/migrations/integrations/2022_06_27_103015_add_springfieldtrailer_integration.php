<?php

use App\Models\User\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSpringfieldtrailerIntegration extends Migration
{

    private const TABLE_NAME = 'integration';

    private const DEALER_INTEGRATION_TABLE_NAME = 'integration_dealer';

    private const INTEGRATION_PARAMS = [
        'integration_id' => 90,
        'code' => 'springfieldtrailer',
        'module_name' => 'springfieldtrailer',
        'module_status' => 'idle',
        'name' => 'Springfield Trailer',
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

    private const INTEGRATION_DEALER_SPRINGFIELDTRAILER = [
        'integration_id' => 90,
        'dealer_id' => 7474,
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
            'description' => 'Your Springfield Trailer match dealer ID.',
            'type' => 'text',
            'required' => false,
        ],
        1 => [
            'name' => 'host',
            'label' => 'Host',
            'description' => 'Ip or domain for ftp',
            'type' => 'text',
            'required' => true,
        ],
        2 => [
            'name' => 'username',
            'label' => 'Username',
            'description' => 'Username for the ftp connection',
            'type' => 'text',
            'required' => true,
        ],
        3 => [
            'name' => 'password',
            'label' => 'Password',
            'description' => 'Password for the ftp connection',
            'type' => 'text',
            'required' => true,
        ],
    ];

    private const DEALER_SETTINGS = [
        'dealer_id' => '7474',
        'host' => 'springfieldtra.sftp.wpengine.com',
        'username' => 'springfieldtra-truckpaper',
        'password' => 'NeOzcdDa1f4G'
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

        // We only insert the record if this dealer exists in the database
        if ($this->checkIntegration() && !$this->checkIntegrationDealer() && $this->checkDealer()) {
            DB::transaction(function () {
                $integration_dealer = self::INTEGRATION_DEALER_SPRINGFIELDTRAILER;
                $integration_dealer['created_at'] = (new DateTime())->format('Y:m:d H:i:s');
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
        DB::table(self::DEALER_INTEGRATION_TABLE_NAME)
            ->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])
            ->delete();

        DB::table(self::TABLE_NAME)
            ->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])
            ->delete();
    }

    /**
     * @return bool
     */
    private function checkIntegration(): bool {
        return DB::table(self::TABLE_NAME)
            ->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])
            ->exists();
    }

    /**
     * @return bool
     */
    private function checkIntegrationDealer(): bool {
        return DB::table(self::DEALER_INTEGRATION_TABLE_NAME)
            ->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])
            ->where('dealer_id', self::INTEGRATION_DEALER_SPRINGFIELDTRAILER['dealer_id'])
            ->exists();
    }

    /**
     * @return bool
     */
    private function checkDealer(): bool {
        return User::where('dealer_id', self::INTEGRATION_DEALER_SPRINGFIELDTRAILER['dealer_id'])->exists();
    }
}

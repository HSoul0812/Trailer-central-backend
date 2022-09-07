<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Carbon\Carbon;
use App\Traits\Migrations\Integrations\SetupAndCheckNew;

class AddAutoSweetIntegration extends Migration
{
    use SetupAndCheckNew;

    private const DEALER_ID = 5305;

    private $integration = [
        'code' => 'autosweet',
        'module_name' => 'autosweet',
        'module_status' => 'idle',
        'name' => 'AutoSweet',
        'description' => "List all your Cars on AutoSweet",
        'domain' => 'https://autosweet.com',
        'create_account_url' => 'https://www.autosweet.com/',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => 'a:0:{}',
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    private $dealer = [
        'dealer_id' => self::DEALER_ID,
        'active' => 1,
        'settings' => [],
        'location_ids' => '',
        'msg_title' => '',
        'msg_body' => '',
        'msg_date' => '0000-00-00 00:00:00',
        'include_pending_sale' => 0
    ];

    /**
     * Run the migrations if the integration doesn't exist.
     *
     * @return void
     */
    public function up()
    {
        // Gets new integrationId if integration doesn't exist.
        $integrationId = $this->getNextId($this->integration['code']);

        if ($integrationId) {
            $this->integration['integration_id'] = $integrationId;
            $this->dealer['integration_id'] = $integrationId;

            DB::transaction(function () {
                DB::table('integration')->insert($this->integration);

                $dealer = $this->dealer;
                $dealer['created_at'] = Carbon::now()->setTimezone('UTC')->toDateTimeString();
                $dealer['settings'] = serialize($dealer['settings']);

                if ($this->dealerExists()) {
                    DB::table('integration_dealer')->insert($dealer);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $integrationId = $this->getIntegrationIdFromCode();

        if ($integrationId) {
            DB::transaction(function () use ($integrationId) {
                DB::table('integration_dealer')
                    ->where('integration_id', $integrationId)
                    ->delete();

                DB::table('integration')
                    ->where('integration_id', $integrationId)
                    ->delete();
            });
        }
    }

    /**
     * Verify if the dealer already exists
     *
     * @return bool
     */
    private function dealerExists(): bool
    {
        return DB::table('dealer')->where('dealer_id', self::DEALER_ID)->exists();
    }

    /**
     * Get integration id from db based on integration code
     *
     * @return int
     */
    private function getIntegrationIdFromCode(): int
    {
        $integration = DB::table('integration')->where('code', $this->integration['code'])->first();

        if ($integration) {
            return $integration->integration_id;
        }

        return 0;
    }
}

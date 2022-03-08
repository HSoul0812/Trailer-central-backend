<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Traits\Migrations\Integrations\SetupAndCheckNew;

class AddCargurusIntegration extends Migration
{
    use SetupAndCheckNew;

    private const CARGURUS_ID = 100;
    private const ALL_SEASONS_POWERSPORTS_ID = 8755;

    private $cargurusIntegration = [
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

    private $allSeasonsPowersports = [
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
     * Run the migrations if the integration doesn't exist.
     *
     * @return void
     */
    public function up()
    {
        if(!$this->integrationCodeExists()) {
            if($this->integrationIdExists()) {
                $this->cargurusIntegration['integration_id'] = $this->getNextIdFromDb();
                $this->allSeasonsPowersports['integration_id'] = $this->getNextIdFromDb();
            }

            DB::transaction(function () {
                DB::table('integration')->insert($this->cargurusIntegration);

                $dealer = $this->allSeasonsPowersports;
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

        if($integrationId) {
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
     * Verify if the integrationId already exists
     *
     * @return bool
     */
    private function integrationIdExists(): bool
    {
        return DB::table('integration')->where('integration_id', $this->cargurusIntegration['integration_id'])->exists();
    }

    /**
     * Verify if the integration already exists
     *
     * @return bool
     */
    private function integrationCodeExists(): bool
    {
        return DB::table('integration')->where('code', $this->cargurusIntegration['code'])->exists();
    }

    /**
     * Verify if the dealer already exists
     *
     * @return bool
     */
    private function dealerExists(): bool
    {
        return DB::table('dealer')->where('dealer_id', self::ALL_SEASONS_POWERSPORTS_ID)->exists();
    }

    /**
     * Get integration id from db based on integration code
     *
     * @return int
     */
    private function getIntegrationIdFromCode(): int
    {
        $integration = DB::table('integration')->where('code', $this->cargurusIntegration['code'])->first();

        if ($integration) {
            return $integration->integration_id;
        }

        return 0;
    }
}

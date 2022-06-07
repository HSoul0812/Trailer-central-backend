<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

use App\Traits\Migrations\Integrations\SetupAndCheckNew;

class AddRynoClassifiedsIntegration extends Migration
{
    use SetupAndCheckNew;

    private const JASON_DIETSCH_ID = 6624;

    private $rynosClassifiedsIntegration = [
        'code' => 'rynos',
        'module_name' => 'rynos',
        'module_status' => 'idle',
        'name' => 'Rynos Classifieds',
        'description' => "List all on Rynos Classifieds",
        'domain' => 'https://ryno.co/',
        'create_account_url' => 'https://ryno.co/register',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => 'a:0:{}',
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    private $jasonDietsch = [
        'dealer_id' => self::JASON_DIETSCH_ID,
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
        // Gets new integrationId if integration doesn't exist.
        $integrationId = $this->getNextId($this->rynosClassifiedsIntegration['code']);

        if($integrationId) {
            $this->rynosClassifiedsIntegration['integration_id'] = $integrationId;
            $this->jasonDietsch['integration_id'] = $integrationId;

            DB::transaction(function () {
                DB::table('integration')->insert($this->rynosClassifiedsIntegration);

                $dealer = $this->jasonDietsch;
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
     * Verify if the dealer already exists
     *
     * @return bool
     */
    private function dealerExists(): bool
    {
        return DB::table('dealer')->where('dealer_id', self::JASON_DIETSCH_ID)->exists();
    }

    /**
     * Get integration id from db based on integration code
     *
     * @return int
     */
    private function getIntegrationIdFromCode(): int
    {
        $integration = DB::table('integration')->where('code', $this->rynosClassifiedsIntegration['code'])->first();

        if ($integration) {
            return $integration->integration_id;
        }

        return 0;
    }
}

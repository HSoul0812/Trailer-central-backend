<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddDealerIdSettingToYachtBrokerIntegration extends Migration
{

    private const SARA_BAY_MARINA = [
        'integration_id' => 81,
        'dealer_id' => 9549
    ];

    private const SARA_BAY_MARINA_SETTINGS = [
        'settings' => 'a:1:{s:9:"dealer_id";s:5:"83232";}'
    ];

    private const YACHT_BROKER_PARAMS = [
        'integration_id' => 81,
    ];

    private const ACTUAL_SETTINGS = [
        'settings' => 'a:0:{}'
    ];

    private const NEW_SETTINGS = [
        'settings' => 'a:1:{i:0;a:5:{s:4:"name";s:9:"dealer_id";s:5:"label";s:9:"Dealer ID";s:11:"description";s:27:"Your YachtBroker Dealer ID.";s:4:"type";s:4:"text";s:8:"required";b:1;}}'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if ($this->checkIntegration()) {
            DB::transaction(function () {
                DB::table('integration')->where(self::YACHT_BROKER_PARAMS)->update(self::NEW_SETTINGS);

                $checkSaraBayMarina = $this->checkSaraBayMarina();
                $currentDateTime = Carbon::now()->setTimezone('UTC')->toDateTimeString();

                if ($checkSaraBayMarina) {
                    DB::table('integration_dealer')->where(self::SARA_BAY_MARINA)->update(self::SARA_BAY_MARINA_SETTINGS);
                } else {
                    DB::table('integration_dealer')->insert([
                        'integration_id' => self::SARA_BAY_MARINA['integration_id'],
                        'dealer_id' => self::SARA_BAY_MARINA['dealer_id'],
                        'created_at' => $currentDateTime,
                        'active' => 1,
                        'settings' => self::SARA_BAY_MARINA_SETTINGS['settings'],
                        'msg_title' => '',
                        'msg_body' => '',
                        'msg_date' => '0000-00-00 00:00:00',
                        'include_pending_sale' => 0
                    ]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $check = $this->checkIntegration();

        if ($check) {
            DB::transaction(function () {
                DB::table('integration_dealer')->where(self::SARA_BAY_MARINA)->update(self::ACTUAL_SETTINGS);
                DB::table('integration')->where(self::YACHT_BROKER_PARAMS)->update(self::ACTUAL_SETTINGS);
            });
        }
    }

    /**
     * @return bool
     */
    private function checkIntegration(): bool
    {
        $checkIntegration = DB::table('integration')->where('integration_id', self::YACHT_BROKER_PARAMS['integration_id'])->exists();

        if ($checkIntegration) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkSaraBayMarina(): bool
    {
        $checkSaraBayMarina = DB::table('dealer')->where('dealer_id', self::SARA_BAY_MARINA['dealer_id'])->exists();

        if ($checkSaraBayMarina) {
            return true;
        }
        return false;
    }
}

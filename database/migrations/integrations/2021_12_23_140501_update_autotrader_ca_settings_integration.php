<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UpdateAutotraderCaSettingsIntegration extends Migration
{

    private const CAPITAL_TRAILERS = [
        'integration_id' => 74,
        'dealer_id' => 8935
    ];

    private const SUNWEST_RV_CENTRE = [
        'integration_id' => 74,
        'dealer_id' => 9696
    ];

    private const INTEGRATION_PARAMS = [
        'integration_id' => 74,
    ];

    private const ACTUAL_SETTINGS = [
        'settings' => 'a:0:{}'
    ];

    private const CAPITAL_TRAILERS_SETTINGS = [
        'settings' => 'a:4:{s:4:"host";s:16:"ftp1.buysell.com";s:8:"username";s:19:"ABCI_CapitalTrailer";s:8:"password";s:8:"VB9rfzve";s:8:"filename";s:23:"ABCI_CapitalTrailer.csv";}'
    ];

    private const SUNWEST_RV_CENTRE_SETTINGS = [
        'settings' => 'a:4:{s:4:"host";s:16:"ftp1.buysell.com";s:8:"username";s:20:"BCCI_SunwestRvCentre";s:8:"password";s:8:"QDYd8TNW";s:8:"filename";s:24:"BCCI_SunwestRvCentre.csv";}'
    ];

    private const NEW_SETTINGS = [
        'settings' => 'a:4:{i:0;a:5:{s:4:"name";s:4:"host";s:5:"label";s:4:"Host";s:11:"description";s:20:"Ip or domain for ftp";s:4:"type";s:4:"text";s:8:"required";b:0;}i:1;a:5:{s:4:"name";s:8:"username";s:5:"label";s:8:"Username";s:11:"description";s:31:"Username for the ftp connection";s:4:"type";s:4:"text";s:8:"required";b:0;}i:2;a:5:{s:4:"name";s:8:"password";s:5:"label";s:8:"Password";s:11:"description";s:31:"Password for the ftp connection";s:4:"type";s:4:"text";s:8:"required";b:0;}i:3;a:5:{s:4:"name";s:8:"filename";s:5:"label";s:8:"filename";s:11:"description";s:65:"Complete Filename for the feed export with .csv extension at end.";s:4:"type";s:4:"text";s:8:"required";b:0;}}'
    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        $check = $this->checkIntegration();

        if ($check) {
            DB::transaction(function () {
                DB::table('integration')->where(self::INTEGRATION_PARAMS)->update(self::NEW_SETTINGS);

                $checkCapitalTrailers = $this->checkCapitalTrailers();

                $currentDateTime = Carbon::now()->setTimezone('UTC')->toDateTimeString();

                if ($checkCapitalTrailers) {
                    DB::table('integration_dealer')->where(self::CAPITAL_TRAILERS)->update(self::CAPITAL_TRAILERS_SETTINGS);
                } else {
                    DB::table('integration_dealer')->insert([
                        'integration_id' => self::CAPITAL_TRAILERS['integration_id'],
                        'dealer_id' => self::CAPITAL_TRAILERS['dealer_id'],
                        'created_at' => $currentDateTime,
                        'active' => 1,
                        'settings' => self::CAPITAL_TRAILERS_SETTINGS['settings'],
                        'msg_title' => '',
                        'msg_body' => '',
                        'msg_date' => '0000-00-00 00:00:00',
                        'include_pending_sale' => 0
                    ]);
                }

                $checkSunwestRvCentre = $this->checkSunwestRvCentre();

                if ($checkSunwestRvCentre) {
                    DB::table('integration_dealer')->where(self::SUNWEST_RV_CENTRE)->update(self::SUNWEST_RV_CENTRE_SETTINGS);
                } else {
                    DB::table('integration_dealer')->insert([
                        'integration_id' => self::SUNWEST_RV_CENTRE['integration_id'],
                        'dealer_id' => self::SUNWEST_RV_CENTRE['dealer_id'],
                        'created_at' => $currentDateTime,
                        'active' => 1,
                        'settings' => self::SUNWEST_RV_CENTRE_SETTINGS['settings'],
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
                DB::table('integration_dealer')->where(self::SUNWEST_RV_CENTRE)->update(self::ACTUAL_SETTINGS);
                DB::table('integration_dealer')->where(self::CAPITAL_TRAILERS)->update(self::ACTUAL_SETTINGS);
                DB::table('integration')->where(self::INTEGRATION_PARAMS)->update(self::ACTUAL_SETTINGS);
            });
        }
    }

    /**
     * @return bool
     */
    private function checkIntegration()
    {
        $checkIntegration = DB::table('integration')->where('integration_id', self::INTEGRATION_PARAMS['integration_id'])->exists();

        if ($checkIntegration) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkCapitalTrailers()
    {
        $checkCapitalTrailers = DB::table('dealer')->where('dealer_id', self::CAPITAL_TRAILERS['dealer_id'])->exists();

        if ($checkCapitalTrailers) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    private function checkSunwestRvCentre()
    {
        $checkSunwestRvCentre = DB::table('dealer')->where('dealer_id', self::SUNWEST_RV_CENTRE['dealer_id'])->exists();

        if ($checkSunwestRvCentre) {
            return true;
        }
        return false;
    }
}

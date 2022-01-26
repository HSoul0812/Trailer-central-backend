<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateV12softwareSettingsIntegration extends Migration
{
    private const INTEGRATION_TABLE = 'integration';

    private const DEALER_INTEGRATION_TABLE = 'integration_dealer';

    private const DEALER_TABLE = 'dealer';

    private const INTEGRATION_ID = 82;

    private const SULLIVAN_RV = [
        'integration_id' => self::INTEGRATION_ID,
        'dealer_id' => 6989
    ];

    private const MICHLS_TRAILER_SALES_LLC = [
        'integration_id' => self::INTEGRATION_ID,
        'dealer_id' => 10627
    ];

    private const SCHRECKS_AUTO = [
        'integration_id' => self::INTEGRATION_ID,
        'dealer_id' => 3953
    ];

    private const INTEGRATION_PARAMS = [
        'integration_id' => self::INTEGRATION_ID,
    ];

    private const ACTUAL_SETTINGS = [
        'settings' => 'a:0:{}'
    ];

    private const DEFAULT_SETTINGS = [
        'settings' => 'a:4:{s:4:"host";s:19:"ftp.v12software.com";s:8:"username";s:7:"45155_2";s:8:"password";s:8:"9v7i9kfu";s:8:"filename";s:9:"45155.csv";}'
    ];

    private const SCHRECKS_AUTO_SETTINGS = [
        'settings' => 'a:4:{s:4:"host";s:19:"ftp.v12software.com";s:8:"username";s:7:"46802_1";s:8:"password";s:8:"tvhwucz4";s:8:"filename";s:9:"46802.csv";}'
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
                DB::table(self::INTEGRATION_TABLE)->where(self::INTEGRATION_PARAMS)->update(self::NEW_SETTINGS);

                $checkSullivanRv = $this->checkSullivanRv();

                if ($checkSullivanRv) {
                    DB::table(self::DEALER_INTEGRATION_TABLE)->where(self::SULLIVAN_RV)->update(self::DEFAULT_SETTINGS);
                }

                $checkMichlsTrailerSales = $this->checkMichlsTrailerSales();

                if ($checkMichlsTrailerSales) {
                    DB::table(self::DEALER_INTEGRATION_TABLE)->where(self::MICHLS_TRAILER_SALES_LLC)->update(self::DEFAULT_SETTINGS);
                }

                $checkSchrecksAuto = $this->checkSchrecksAuto();

                if ($checkSchrecksAuto) {
                    DB::table(self::DEALER_INTEGRATION_TABLE)->where(self::SCHRECKS_AUTO)->update(self::SCHRECKS_AUTO_SETTINGS);
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
                DB::table(self::DEALER_INTEGRATION_TABLE)->where(self::INTEGRATION_PARAMS)->update(self::ACTUAL_SETTINGS);
                DB::table(self::INTEGRATION_TABLE)->where(self::INTEGRATION_PARAMS)->update(self::ACTUAL_SETTINGS);
            });
        }
    }

    /**
     * @return bool
     */
    private function checkIntegration(): bool
    {
        $checkIntegration = DB::table(self::INTEGRATION_TABLE)->where(self::INTEGRATION_PARAMS)->exists();

        if ($checkIntegration) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkSullivanRv(): bool
    {
        $checkSullivanRv = DB::table(self::DEALER_TABLE)->where('dealer_id', self::SULLIVAN_RV['dealer_id'])->exists();

        if ($checkSullivanRv) {
            return true;
        }
        return false;
    }


    /**
     * @return bool
     */
    private function checkMichlsTrailerSales(): bool
    {
        $checkMichlsTrailerSales = DB::table(self::DEALER_TABLE)->where('dealer_id', self::MICHLS_TRAILER_SALES_LLC['dealer_id'])->exists();

        if ($checkMichlsTrailerSales) {
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    private function checkSchrecksAuto(): bool
    {
        $checkSchrecksAuto = DB::table(self::DEALER_TABLE)->where('dealer_id', self::SCHRECKS_AUTO['dealer_id'])->exists();

        if ($checkSchrecksAuto) {
            return true;
        }
        return false;
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class UpdateAuction123SettingsIntegration extends Migration
{
    private const TABLE_NAME = 'integration';

    private const INTEGRATION_PARAMS = [
        'integration_id' => 35,
    ];

    private const ACTUAL_SETTINGS = [
        'settings' => 'a:1:{i:0;a:5:{s:4:"name";s:8:"username";s:5:"label";s:13:"Auction123 ID";s:11:"description";s:21:"Your ID in Auction123";s:4:"type";s:4:"text";s:8:"required";b:1;}}'
    ];

    private const NEW_SETTINGS = [
        'settings' => 'a:2:{i:0;a:5:{s:4:"name";s:8:"username";s:5:"label";s:13:"Auction123 ID";s:11:"description";s:21:"Your ID in Auction123";s:4:"type";s:4:"text";s:8:"required";b:1;}i:1;a:6:{s:4:"name";s:7:"package";s:5:"label";s:7:"Package";s:11:"description";s:58:"How many active listings your Auction 123 account supports";s:4:"type";s:6:"select";s:7:"options";a:4:{i:100;i:100;i:250;i:250;i:500;i:500;i:1;s:9:"Unlimited";}s:8:"required";b:1;}}'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        if ($this->checkIntegration()) {
            DB::transaction(function () {
                DB::table(self::TABLE_NAME)->where(self::INTEGRATION_PARAMS)->update(self::NEW_SETTINGS);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        if ($this->checkIntegration()) {
            DB::transaction(function () {
                DB::table(self::TABLE_NAME)->where(self::INTEGRATION_PARAMS)->update(self::ACTUAL_SETTINGS);
            });
        }
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
}

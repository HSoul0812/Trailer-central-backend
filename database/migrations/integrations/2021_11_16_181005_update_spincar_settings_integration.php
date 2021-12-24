<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateSpincarSettingsIntegration extends Migration
{
    private const INTEGRATION_PARAMS = [
        'integration_id' => 69,
    ];

    private const ACTUAL_SETTINGS = [
        'settings' => 'a:1:{i:0;a:5:{s:4:"name";s:8:"username";s:5:"label";s:13:"Telephone No.";s:11:"description";s:36:"Your Phone number.";s:4:"type";s:4:"text";s:8:"required";b:1;}}'
    ];

    private const NEW_SETTINGS = [
        'settings' => 'a:2:{i:0;a:5:{s:4:"name";s:8:"username";s:5:"label";s:13:"Telephone No.";s:11:"description";s:18:"Your Phone number.";s:4:"type";s:4:"text";s:8:"required";b:1;}i:1;a:5:{s:4:"name";s:22:"dealer_location_new_id";s:5:"label";s:26:"Map New Dealer Location ID";s:11:"description";s:47:"Place the New location Id for the actual Dealer";s:4:"type";s:4:"text";s:8:"required";b:0;}}'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('integration')->where(self::INTEGRATION_PARAMS)->update(self::NEW_SETTINGS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('integration')->where(self::INTEGRATION_PARAMS)->update(self::ACTUAL_SETTINGS);
    }
}

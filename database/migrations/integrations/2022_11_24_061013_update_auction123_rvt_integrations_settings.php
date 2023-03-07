<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAuction123RvtIntegrationsSettings extends Migration
{
    private const TABLE = 'integration';

    private const INTEGRATION_IDS_FIELD = [
        4 => 'show_on_rvt',
        35 => 'show_on_auction123',
        36 => 'show_on_auction123'
    ];

    private const ACTIVATE_SETTINGS = [
        'name' => 'activate_all',
        'label' => 'Activate all existing units after saving',
        'type' => 'checkbox',
        'description' => '',
        'default' => 1,
        'required' => 1,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::INTEGRATION_IDS_FIELD as $integrationId => $field) {
            $integration = DB::table(self::TABLE)
                ->select('settings')
                ->where('integration_id', $integrationId)
                ->first();

            $activateSettings = self::ACTIVATE_SETTINGS;
            $activateSettings['field'] = $field;

            $settings = unserialize($integration->settings);
            $settings[] = $activateSettings;

            DB::table(self::TABLE)
                ->where('integration_id', $integrationId)
                ->update(['settings' => serialize($settings)]);
        }
    }
}

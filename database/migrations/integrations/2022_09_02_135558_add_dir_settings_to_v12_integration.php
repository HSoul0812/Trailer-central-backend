<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDirSettingsToV12Integration extends Migration
{
    private const INTEGRATION_ID = 82;

    private const OLD_SETTINGS = array (
        array (
            'name' => 'host',
            'label' => 'Host',
            'description' => 'Ip or domain for ftp',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'username',
            'label' => 'Username',
            'description' => 'Username for the ftp connection',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'password',
            'label' => 'Password',
            'description' => 'Password for the ftp connection',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'filename',
            'label' => 'Filename',
            'description' => 'Complete Filename for the feed export with .csv extension at end.',
            'type' => 'text',
            'required' => true,
        ),
    );

    private const NEW_SETTINGS = array (
        array (
            'name' => 'host',
            'label' => 'Host',
            'description' => 'Ip or domain for ftp',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'dir',
            'label' => 'Directory',
            'description' => 'Directory path to drop files',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'username',
            'label' => 'Username',
            'description' => 'Username for the ftp connection',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'password',
            'label' => 'Password',
            'description' => 'Password for the ftp connection',
            'type' => 'text',
            'required' => true,
        ),
        array (
            'name' => 'filename',
            'label' => 'Filename',
            'description' => 'Complete Filename for the feed export with .csv extension at end.',
            'type' => 'text',
            'required' => true,
        ),
    );
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->updateIntegration(self::INTEGRATION_ID, self::NEW_SETTINGS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->updateIntegration(self::INTEGRATION_ID, self::OLD_SETTINGS);
    }

    /**
     * Check if integration exists
     *
     * @param int $integrationId
     * @return bool
     */
    public function checkIntegration(int $integrationId): bool
    {
        return DB::table('integration')->where('integration_id', $integrationId)->exists();
    }

    /**
     * Update integration
     *
     * @param int $integrationId
     * @param array $settings
     * @return void
     */
    public function updateIntegration(int $integrationId, array $settings)
    {
        if ($this->checkIntegration($integrationId)) {
            DB::table('integration')->where('integration_id', $integrationId)->update(['settings' => serialize($settings)]);
        }
    }
}

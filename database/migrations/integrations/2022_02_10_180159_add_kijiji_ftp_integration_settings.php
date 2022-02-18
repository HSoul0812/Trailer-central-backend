<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKijijiFtpIntegrationSettings extends Migration
{
    private const KIJIJIFTP_ID = 86;
    private const KIJIJIFTP_SETTINGS = 'a:3:{i:0;a:5:{s:4:"name";s:8:"username";s:5:"label";s:8:"Username";s:11:"description";s:12:"FTP Username";s:4:"type";s:4:"text";s:7:"requied";i:1;}i:1;a:5:{s:4:"name";s:8:"password";s:5:"label";s:8:"Password";s:11:"description";s:12:"FTP Password";s:4:"type";s:4:"text";s:7:"requied";i:1;}i:2;a:5:{s:4:"name";s:5:"email";s:5:"label";s:6:"E-Mail";s:11:"description";s:31:"Your Kijiji registration E-Mail";s:4:"type";s:4:"text";s:8:"required";i:1;}}';

    private const KIJIJIFTP_PARAMS = [
        'integration_id' => self::KIJIJIFTP_ID,
        'code' => 'kijijiftp',
        'module_name' => 'kijijiftp',
        'module_status' => 'idle',
        'name' => 'KijijiFTP',
        'description' => 'List all your trailers on Kijiji',
        'domain' => 'www.kijiji.ca',
        'create_account_url' => 'http://www.kijiji.ca',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => SELF::KIJIJIFTP_SETTINGS,
        'include_sold' => 0,
        'uses_staging' => 1,
        'show_for_integrated' => 0
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('integration')
            ->insert(self::KIJIJIFTP_PARAMS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration')
            ->where('integration_id', self::KIJIJIFTP_ID)
            ->delete();
    }
}

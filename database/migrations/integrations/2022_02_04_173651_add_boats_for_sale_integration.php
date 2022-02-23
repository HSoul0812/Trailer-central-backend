<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBoatsForSaleIntegration extends Migration
{
    private const BOATSFORSALE_ID = 85;
    private const BOATSFORSALE_SETTINGS = 'a:1:{i:0;a:5:{s:4:"name";s:9:"dealer_id";s:5:"label";s:10:"Account Id";s:11:"description";s:28:"Your BoatsForSale Account Id";s:4:"type";s:4:"text";s:8:"required";i:1;}}';

    private const BOATSFORSALE_PARAMS = [
        'integration_id' => self::BOATSFORSALE_ID,
        'code' => 'boatsforsale',
        'module_name' => 'boatsforsale',
        'module_status' => 'idle',
        'name' => 'BoatsForSale',
        'description' => 'List all your Boats on BoatsForSale',
        'domain' => 'www.boatsforsale.com',
        'create_account_url' => 'https://www.boatsforsale.com/identity/account/register',
        'active' => 1,
        'filters' => 'a:0:{}',
        'frequency' => 21600,
        'settings' => SELF::BOATSFORSALE_SETTINGS,
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
            ->insert(self::BOATSFORSALE_PARAMS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration')
            ->where('integration_id', self::BOATSFORSALE_ID)
            ->delete();
    }
}

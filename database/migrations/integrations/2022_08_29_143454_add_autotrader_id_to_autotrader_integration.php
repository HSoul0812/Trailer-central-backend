<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutotraderIdToAutotraderIntegration extends Migration
{
    private const INTEGRATION_ID = 68;

    private const OLD_SETTINGS = 'a:1:{i:0;a:2:{s:6:"filter";a:1:{i:0;a:3:{s:5:"field";s:8:"category";s:5:"value";s:5:"horse";s:8:"operator";s:2:"or";}}s:8:"operator";s:3:"and";}}';
    private const NEW_SETTINGS = 'a:1:{i:0;a:5:{s:4:"name";s:13:"autotrader_id";s:5:"label";s:13:"AutoTrader ID";s:11:"description";s:20:"Your AutroTrader ID.";s:4:"type";s:4:"text";s:8:"required";b:1;}}';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('integration')->where('integration_id', self::INTEGRATION_ID)->update(['settings' => self::NEW_SETTINGS]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration')->where('integration_id', self::INTEGRATION_ID)->update(['settings' => self::OLD_SETTINGS]);
    }
}

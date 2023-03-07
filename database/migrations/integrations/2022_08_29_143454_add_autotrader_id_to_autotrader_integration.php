<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutotraderIdToAutotraderIntegration extends Migration
{
    private const INTEGRATION_ID = 68;

    private const FILTERS = array(
        array(
            "filter" => array(
                "field" => "category",
                "value" => "horse",
                "operator" => "or"
            ),
            "operator" => "and"
        )
    );

    private const SETTINGS = array(
        array(
            "name" => "autotrader_id",
            "label" => "AutoTrader ID",
            "description" => "Your AutroTrader ID.",
            "type" => "text",
            "required" => true
        )
    );

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('integration')->where('integration_id', self::INTEGRATION_ID)->update([
            'filters' => serialize(self::FILTERS),
            'settings' => serialize(self::SETTINGS)
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('integration')->where('integration_id', self::INTEGRATION_ID)->update([
            'filters' => serialize(array()),
            'settings' => serialize(array())
        ]);
    }
}

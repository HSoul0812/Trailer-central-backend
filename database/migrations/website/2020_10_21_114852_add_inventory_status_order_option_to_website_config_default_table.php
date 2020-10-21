<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInventoryStatusOrderOptionToWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_STATUS_ORDER_KEY = 'inventory/status_order';

    private const INVENTORY_STATUS_ORDER_OPTION = [
        'is_featured:desc,1,3,4' => 'Featured > Available > On Order > Pending Sale',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $config = DB::table('website_config_default')
            ->select('values')
            ->where('key', self::INVENTORY_STATUS_ORDER_KEY)
            ->first();

        $values = json_decode($config->values, true);
        $values = $values + self::INVENTORY_STATUS_ORDER_OPTION;

        DB::table('website_config_default')
            ->where('key', self::INVENTORY_STATUS_ORDER_KEY)
            ->update(['values' => json_encode($values)]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $config = DB::table('website_config_default')
            ->select('values')
            ->where('key', self::INVENTORY_STATUS_ORDER_KEY)
            ->first();

        $values = json_decode($config->values, true);
        $stockValues = array_keys(self::INVENTORY_STATUS_ORDER_OPTION);

        unset($values[$stockValues[0]]);

        DB::table('website_config_default')
            ->where('key', self::INVENTORY_STATUS_ORDER_KEY)
            ->update(['values' => json_encode($values)]);
    }
}

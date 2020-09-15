<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateDefaultInventoryOrderInWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_DEFAULT_ORDER_KEY = 'inventory/default_order';

    private const INVENTORY_ORDER_VALUES_STOCK = [
        8 => 'Stock (Asc)',
        9 => 'Stock (Desc)',
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
            ->where('key', self::INVENTORY_DEFAULT_ORDER_KEY)
            ->first();

        $values = json_decode($config->values, true);
        $values = $values + self::INVENTORY_ORDER_VALUES_STOCK;

        DB::table('website_config_default')
            ->where('key', self::INVENTORY_DEFAULT_ORDER_KEY)
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
            ->where('key', self::INVENTORY_DEFAULT_ORDER_KEY)
            ->first();

        $values = json_decode($config->values, true);
        $stockValues = array_keys(self::INVENTORY_ORDER_VALUES_STOCK);

        unset($values[$stockValues[0]]);
        unset($values[$stockValues[1]]);

        DB::table('website_config_default')
            ->where('key', self::INVENTORY_DEFAULT_ORDER_KEY)
            ->update(['values' => json_encode($values)]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInventoryCompareToWebsiteConfigDefaultTable extends Migration
{
    private const WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION = [
        'key' => 'inventory/compare',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'It allows your users to compare multiple units with each other',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"do_not_show": "Do Not Show Compare", "show":"Show Compare"}',
        'default_label' => '',
        'default_value' => 'do_not_show',
        'sort_order' => 1320,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION['key'])->delete();
    }
}

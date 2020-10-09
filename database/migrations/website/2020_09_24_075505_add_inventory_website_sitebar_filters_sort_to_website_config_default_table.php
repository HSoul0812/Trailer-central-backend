<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInventoryWebsiteSitebarFiltersSortToWebsiteConfigDefaultTable extends Migration
{
    private const WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION = [
        'key' => 'inventory/website_sidebar_filters_order',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'It allows to set sidebar filters sorting on the website',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"countDesc":"Sort By Count (DESC)", "countAsc": "Sort By Count (ASC)", "nameDesc":"Sort By Name (DESC)", "nameAsc":"Sort By Name (ASC)"}',
        'default_label' => '',
        'default_value' => 'countDesc',
        'sort_order' => 1120,
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

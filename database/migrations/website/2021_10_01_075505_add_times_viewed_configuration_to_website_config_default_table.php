<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddTimesViewedConfigurationToWebsiteConfigDefaultTable extends Migration
{
    private const WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION = [
        'key' => 'inventory/times_viewed',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'It shows how many times your units have been viewed by visitors',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"do_not_show": "Do Not Show Times Viewed", "show":"Show Times Viewed"}',
        'default_label' => '',
        'default_value' => 'do_not_show',
        'sort_order' => 1220,
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

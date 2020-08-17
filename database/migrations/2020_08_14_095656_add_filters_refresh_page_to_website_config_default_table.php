<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFiltersRefreshPageToWebsiteConfigDefaultTable extends Migration
{
    private const FILTERS_REFRESH_PAGE_OPTION = [
        'key' => 'inventory/filters_refresh_page_option',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Filters Refresh Page',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"check_filter":"Check Filter","update_button":"Update Button"}',
        'default_label' => '',
        'default_value' => 'check_filter',
        'sort_order' => 1100,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::FILTERS_REFRESH_PAGE_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::FILTERS_REFRESH_PAGE_OPTION['key'])->delete();
    }
}

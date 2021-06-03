<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBrandFilterVisibilityToWebsiteConfigDefaultTable extends Migration
{
    private const FILTERS_VISIBILITY_BRAND_OPTION = [
        'key' => 'inventory/filters/visibility_brand',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Toggle Visibility of Brand Filter',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"0":"Hide 0\'s","1":"Show All"}',
        'default_label' => 'Show All',
        'default_value' => null,
        'sort_order' => 532,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::FILTERS_VISIBILITY_BRAND_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::FILTERS_VISIBILITY_BRAND_OPTION['key'])->delete();
    }
}
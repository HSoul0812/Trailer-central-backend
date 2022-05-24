<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIncludeDescriptionOnSearchToWebsiteConfigDefaultTable extends Migration
{
    private const INCLUDE_DESCRIPTION_ON_KEYWORD_SEARCH = [
        'key' => 'inventory/include_description_on_search',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Include description field on keyword search',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"0":"No","1":"Yes"}',
        'default_label' => 'Yes',
        'default_value' => '1',
        'sort_order' => 1324,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::INCLUDE_DESCRIPTION_ON_KEYWORD_SEARCH);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::INCLUDE_DESCRIPTION_ON_KEYWORD_SEARCH['key'])->delete();
    }
}

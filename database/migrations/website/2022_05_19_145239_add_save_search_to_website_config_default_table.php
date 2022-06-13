<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSaveSearchToWebsiteConfigDefaultTable extends Migration
{
    private const SHOW_SAVE_SEARCH = [
        'key' => 'website/show_save_search',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Show Save Search',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"0":"No","1":"Yes"}',
        'default_label' => 'No',
        'default_value' => null,
        'sort_order' => 1323,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOW_SAVE_SEARCH);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::SHOW_SAVE_SEARCH['key'])->delete();
    }
}

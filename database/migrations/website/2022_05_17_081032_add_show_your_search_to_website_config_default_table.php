<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShowYourSearchToWebsiteConfigDefaultTable extends Migration
{
    private const SHOW_YOUR_SEARCH = [
        'key' => 'website/show_your_search',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Show Your Search',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"0":"No","1":"Yes"}',
        'default_label' => 'No',
        'default_value' => null,
        'sort_order' => 1321,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOW_YOUR_SEARCH);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::SHOW_YOUR_SEARCH['key'])->delete();
    }
}

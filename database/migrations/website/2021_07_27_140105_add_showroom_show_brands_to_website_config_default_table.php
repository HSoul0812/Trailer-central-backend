<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShowroomShowBrandsToWebsiteConfigDefaultTable extends Migration
{
    private const SHOWROOM_SHOW_BRANDS_OPTION = [
        'key' => 'showroom/show_brands',
        'private' => 1,
        'type' => 'checkbox',
        'label' => 'Include Brands',
        'grouping' => '',
        'default_label' => '',
        'sort_order' => 1100,
    ];

    private const SHOWROOM_BRANDS_OPTION = [
        'key' => 'showroom/brands',
        'private' => 1,
        'type' => 'enumerable',
        'label' => 'Brands',
        'grouping' => '',
        'default_label' => '',
        'sort_order' => 1100,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::SHOWROOM_SHOW_BRANDS_OPTION);
        DB::table('website_config_default')->insert(self::SHOWROOM_BRANDS_OPTION);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::SHOWROOM_SHOW_BRANDS_OPTION['key'])->delete();
        DB::table('website_config_default')->where('key', self::SHOWROOM_BRANDS_OPTION['key'])->delete();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddHomeCarouselCustomListingsToWebsiteConfigDefaultTable extends Migration
{
    private const WASATCH_TRAILER_WEBSITE_ID = 206;

    private const CAROUSEL_CUSTOM_LISTS_OPTION = [
        'key' => 'home/custom_carousel_lists',
        'private' => 1,
        'type' => 'text',
        'label' => 'Custom Carousel Lists',
        'note' => null,
        'grouping' => 'Home Page Display',
        'values' => null,
        'default_label' => '',
        'default_value' => null,
        'sort_order' => 1500,
    ];

    private const WASATCH_CUSTOM_CAROUSEL_CONFIG = [
        'website_id' => self::WASATCH_TRAILER_WEBSITE_ID,
        'key' => self::CAROUSEL_CUSTOM_LISTS_OPTION['key'],
        'value' => [
            [
                'custom_list_title' => 'Latest Arrivals - Layton',
                'dealer_location_id' => 10882,
                'is_featured' => false
            ],
            [
                'custom_list_title' => 'Latest Arrivals - Springville',
                'dealer_location_id' => 14349,
                'is_featured' => false
            ],
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::CAROUSEL_CUSTOM_LISTS_OPTION);

        $config = self::WASATCH_CUSTOM_CAROUSEL_CONFIG;
        $config['value'] = json_encode($config['value']);

        DB::table('website_config')->insert($config);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::CAROUSEL_CUSTOM_LISTS_OPTION['key'])->delete();
    }
}

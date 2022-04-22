<?php

use Illuminate\Database\Migrations\Migration;

class AddPaymentCalculatorTermListToWebsiteConfigDefault extends Migration
{
    private const WEBSITE_SIDEBAR_FILTERS_ORDER_OPTION = [
        'key' => 'payment-calculator/term-list',
        'private' => 0,
        'type' => 'enumerable_multiple',
        'label' => 'Terms list',
        'note' => null,
        'grouping' => 'Details Page Payment Calculator Term Options',
        'values' => '{"1":"12 Months (1 Year)","2":"24 Months (2 Years)","3":"36 Months (3 Years)","4":"48 Months (4 Years)","5":"60 Months (5 Years)","6":"72 Months (6 Years)","7":"84 Months (7 Years)","8":"96 Months (8 Years)","9":"108 Months (9 Years)","10":"120 Months (10 Years)","11":"132 Months (11 Years)","12":"144 Months (12 Years)","13":"156 Months (13 Years)","14":"168 Months (14 Years)","15":"180 Months (15 Years)","16":"192 Months (16 Years)","17":"204 Months (17 Years)","18":"216 Months (18 Years)","19":"228 Months (19 Years)","20":"240 Months (20 Years)"}',
        'default_label' => '',
        'default_value' => '["1","2","3","4","5","6","7","8","9","10","11","12","13","14","15","16","17","18","19","20"]',
        'sort_order' => 2620
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

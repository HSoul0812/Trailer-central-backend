<?php

use Illuminate\Database\Migrations\Migration;

class FixBrandFilterVisibilityOnWebsiteConfigDefault extends Migration
{
    private const FILTERS_VISIBILITY_BRAND_KEY = 'inventory/filters/visibility_brand';
    
    private const FILTERS_VISIBILITY_BRAND_VALUES = '{"0":"Show All","1":"Hide 0\'s"}';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')
          ->where('key', self::FILTERS_VISIBILITY_BRAND_KEY)
          ->update(['values' => self::FILTERS_VISIBILITY_BRAND_OPTION]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}

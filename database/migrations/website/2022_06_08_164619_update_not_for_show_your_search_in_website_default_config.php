<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateNotForShowYourSearchInWebsiteDefaultConfig extends Migration
{
    const KEY = 'website/show_your_search';
    const NOTE = 'Also requires "Filters Refresh Page" configuration to be set to "Update Button"';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')
            ->where('key', self::KEY)
            ->update([
                'note' => self::NOTE
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')
            ->where('key', self::KEY)
            ->update([
                'note' => null
            ]);
    }
}

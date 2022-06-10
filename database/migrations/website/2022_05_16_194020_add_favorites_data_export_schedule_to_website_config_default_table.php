<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFavoritesDataExportScheduleToWebsiteConfigDefaultTable extends Migration
{
    private const FAVORITES_DATA_EXPORT_SCHEDULE = [
        'key' => 'general/favorites_export_schedule',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Schedule for Exporting Favorites',
        'note' => null,
        'grouping' => 'General',
        'values' => '{"0":"Daily","1":"Weekly","2":"Bi-Weekly","3":"Monthly"}',
        'default_label' => 'Daily',
        'default_value' => null,
        'sort_order' => 2690,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::FAVORITES_DATA_EXPORT_SCHEDULE);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::FAVORITES_DATA_EXPORT_SCHEDULE['key'])->delete();
    }
}

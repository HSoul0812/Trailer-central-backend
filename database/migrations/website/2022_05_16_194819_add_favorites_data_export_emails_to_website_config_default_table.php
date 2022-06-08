<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddFavoritesDataExportEmailsToWebsiteConfigDefaultTable extends Migration
{
    private const FAVORITES_DATA_EXPORT_EMAILS = [
        'key' => 'general/favorites_export_emails',
        'private' => 0,
        'type' => 'text',
        'label' => 'Emails to receive favorites export',
        'note' => 'Use semi-colon(;) to separate multiple emails',
        'grouping' => 'General',
        'values' => null,
        'default_label' => '',
        'default_value' => null,
        'sort_order' => 2691,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::FAVORITES_DATA_EXPORT_EMAILS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::FAVORITES_DATA_EXPORT_EMAILS['key'])->delete();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddLeadsMergeEnableConfigDefault extends Migration
{
    private const LEADS_MERGE_ENABLE_DATA = [
        'key' => 'leads/merge/enabled',
        'private' => 0,
        'type' => 'checkbox',
        'label' => 'Enable/Disable Lead Merge',
        'note' => null,
        'grouping' => 'Website',
        'values' => null,
        'default_label' => '',
        'default_value' => 1,
        'sort_order' => 1520,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::LEADS_MERGE_ENABLE_DATA);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::LEADS_MERGE_ENABLE_DATA['key'])->delete();
    }
}

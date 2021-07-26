<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInventoryDurationBeforeAutoArchivingToWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_DURATION_BEFORE_AUTO_ARCHIVING = [
        'key' => 'inventory/duration_before_auto_archiving',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Duration of Sold Unit Before Auto-Archiving',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"-1":"Manually","0":"Immediately","1":"24 Hours","2":"48 Hours","7":"7 Days","14":"2 Weeks","21":"3 Weeks","28":"4 Weeks"}',
        'default_label' => 'Manually',
        'default_value' => '-1',
        'sort_order' => 1040,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('website_config_default')->insert(self::INVENTORY_DURATION_BEFORE_AUTO_ARCHIVING);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('website_config_default')->where('key', self::INVENTORY_DURATION_BEFORE_AUTO_ARCHIVING['key'])->delete();
    }
}

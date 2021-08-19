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
        'values' => '{"0":"Auto Archive (immediately)","24":"24 Hours","48":"48 Hours","168":"7 Days","336":"2 Weeks","504":"3 Weeks","672":"4 Weeks"}',
        'default_label' => 'Auto Archive (immediately)',
        'default_value' => '0',
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

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddInventorySourceWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_SOURCE = [
        'key' => 'inventory/source',
        'private' => 1,
        'type' => 'enumerable',
        'label' => 'Inventory source',
        'note' => 'Determines where the inventory should be pulled from',
        'grouping' => 'Inventory Display',
        'values' => '{"env":"Environment variable","es":"ElasticSearch", "sdk": "SDK"}',
        'default_label' => 'Environment variable',
        'default_value' => 'env',
        'sort_order' => 1326
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->insert(self::INVENTORY_SOURCE);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::INVENTORY_SOURCE['key'])->delete();
    }
}

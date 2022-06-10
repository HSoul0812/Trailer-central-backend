<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddUseProximitySelectorToWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_SHOW_PROXIMITY = [
        'key' => 'website/use_proximity_distance_selector',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Show proximity distance selector',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"0":"No","1":"Yes"}',
        'default_label' => 'No',
        'default_value' => '0',
        'sort_order' => 1324,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->insert(self::INVENTORY_SHOW_PROXIMITY);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::INVENTORY_SHOW_PROXIMITY['key'])->delete();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSortByRelevanceToWebsiteConfigDefaultTable extends Migration
{
    private const INVENTORY_USE_RELEVANCE_SORTING = [
        'key' => 'inventory/sort_by_relevance',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Use relevance sorting method when customer use `keyword` search',
        'note' => null,
        'grouping' => 'Inventory Display',
        'values' => '{"0":"No","1":"Yes"}',
        'default_label' => 'No',
        'default_value' => '0',
        'sort_order' => 1325,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->insert(self::INVENTORY_USE_RELEVANCE_SORTING);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::INVENTORY_USE_RELEVANCE_SORTING['key'])->delete();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddShowroomLoadFromLinkedDealersToWebsiteConfigDefaultTable extends Migration
{
    private const SHOWROOM_INCLUDE_DEALERS_FROM_GLOBAL_FILTERS = [
        'key' => 'showroom/load_from_linked_dealers_in_global_filters',
        'private' => 0,
        'type' => 'enumerable',
        'label' => 'Include showrooms and manufacturers from dealers linked in global filters',
        'note' => null,
        'grouping' => 'Showroom Setup',
        'values' => '{"1":"Yes","0":"No"}',
        'default_label' => 'Yes',
        'default_value' => '1',
        'sort_order' => 2692,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::table('website_config_default')->insert(self::SHOWROOM_INCLUDE_DEALERS_FROM_GLOBAL_FILTERS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::table('website_config_default')->where('key', self::SHOWROOM_INCLUDE_DEALERS_FROM_GLOBAL_FILTERS['key'])->delete();
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddNumberBatteriesFilterToInventoryFilterTable extends Migration
{
    private const INVENTORY_FILTER_DRY_WEIGHT_PARAMS = [
        'attribute' => 'number_batteries',
        'label' => '# Batteries',
        'type' => 'select',
        'is_eav' => 1,
        'position' => 300,
        'is_visible' => 0
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('inventory_filter')->insert(self::INVENTORY_FILTER_DRY_WEIGHT_PARAMS);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('inventory_filter')
            ->where('attribute', self::INVENTORY_FILTER_DRY_WEIGHT_PARAMS['attribute'])
            ->delete();
    }
}

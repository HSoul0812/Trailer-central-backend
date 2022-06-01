<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIsTiltFilterToInventoryFilterTable extends Migration
{
    private const INVENTORY_FILTER_DRY_WEIGHT_PARAMS = [
        'attribute' => 'tilt',
        'label' => 'Is TILT',
        'type' => 'select',
        'is_eav' => 1,
        'position' => 300,
        'is_visible' => 1
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

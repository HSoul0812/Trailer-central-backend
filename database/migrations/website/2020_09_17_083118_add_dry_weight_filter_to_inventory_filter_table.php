<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDryWeightFilterToInventoryFilterTable extends Migration
{
    private const INVENTORY_FILTER_DRY_WEIGHT_PARAMS = [
        'attribute' => 'dry_weight',
        'label' => 'Dry Weight',
        'type' => 'slider',
        'is_eav' => 1,
        'position' => 300,
        'step' => 1,
        'is_visible' => 0,
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

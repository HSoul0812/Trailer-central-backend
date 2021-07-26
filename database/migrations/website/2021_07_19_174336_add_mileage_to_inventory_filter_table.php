<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMileageToInventoryFilterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('inventory_filter')->insert([
            'attribute' => 'mileage_miles',
            'label' => 'Mileage (mi)',
            'type' => 'slider',
            'position' => 170,
            'step' => 100,
            'is_eav' => 0,
            'dependancy' => 'inventory/filters/toggle_miles_kilometres'
        ]);

        DB::table('inventory_filter')->insert([
            'attribute' => 'mileage_kilometres',
            'label' => 'Mileage (km)',
            'type' => 'slider',
            'position' => 170,
            'step' => 100,
            'is_eav' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('inventory_filter')->where('attribute', '=', 'mileage_miles')->delete();
        DB::table('inventory_filter')->where('attribute', '=', 'mileage_kilometres')->delete();
    }
}

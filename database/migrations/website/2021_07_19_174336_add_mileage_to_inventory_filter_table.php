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
            'attribute' => 'mileage',
            'label' => 'Mileage',
            'type' => 'slider',
            'position' => 600,
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
        DB::table('inventory_filter')->where('attribute', '=', 'mileage')->delete();
    }
}

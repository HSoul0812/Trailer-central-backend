<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDbFieldToInventoryFilterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_filter', function (Blueprint $table) {
            $table->string('db_field')->nullable();
        });

        DB::table('inventory_filter')
            ->where('attribute', '=', 'mileage_miles')
            ->orWhere('attribute', '=', 'mileage_kilometres')
            ->update([
                'is_eav' => 1,
                'db_field' => 'mileage',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_filter', function (Blueprint $table) {
            $table->dropColumn('db_field');
        });
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateTRuckBedsInPartCategories extends Migration
{
    /**
     * Run the migrations.
     */
    public const CATEGORY_NAMES = [['from' => 'Truck Beds', 'to' => 'Truck Beds Category']];

    public function up()
    {
        foreach (self::CATEGORY_NAMES as $nameMap) {
            DB::table('part_categories')->where('name', $nameMap['from'])->update(
                ['name' => $nameMap['to']]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        foreach (self::CATEGORY_NAMES as $nameMap) {
            DB::table('part_categories')->where('name', $nameMap['to'])->update(
                ['name' => $nameMap['from']]
            );
        }
    }
}

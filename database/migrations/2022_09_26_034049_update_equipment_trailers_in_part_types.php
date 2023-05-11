<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateEquipmentTrailersInPartTypes extends Migration
{
    /**
     * Run the migrations.
     */
    public const TYPE_NAMES = [['from' => 'Equipment Trailers', 'to' => 'General Trailers']];

    public function up()
    {
        foreach (self::TYPE_NAMES as $nameMap) {
            DB::table('part_types')->where('name', $nameMap['from'])->update(
                ['name' => $nameMap['to']]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        foreach (self::TYPE_NAMES as $nameMap) {
            DB::table('part_types')->where('name', $nameMap['to'])->update(
                ['name' => $nameMap['from']]
            );
        }
    }
}

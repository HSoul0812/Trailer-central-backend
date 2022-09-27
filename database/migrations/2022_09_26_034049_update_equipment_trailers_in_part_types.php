<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateEquipmentTrailersInPartTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    const TYPE_NAMES = [['from' => 'Equipment Trailers', 'to' => 'General Trailers']];
    public function up()
    {
        foreach(self::TYPE_NAMES as $nameMap) {
            DB::table('part_types')->where('name', $nameMap['from'])->update(
                ['name' => $nameMap['to']]
            );
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach(self::TYPE_NAMES as $nameMap) {
            DB::table('part_types')->where('name', $nameMap['to'])->update(
                ['name' => $nameMap['from']]
            );
        }
    }
}

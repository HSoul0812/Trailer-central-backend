<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddFieldsToShowroomFieldsMappingTable extends Migration
{
    private const SHOWROOM_MAPPING = [
        [
            'type' => 'inventory',
            'map_from' => 'payload_capacity',
            'map_to' => 'payload_capacity',
        ],
        [
            'type' => 'attribute',
            'map_from' => 'series',
            'map_to' => 'series',
        ],
        [
            'type' => 'attribute',
            'map_from' => 'hitch',
            'map_to' => 'pull_type',
        ],
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::SHOWROOM_MAPPING as $mapping) {
            DB::table('showroom_fields_mapping')->insert($mapping);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (self::SHOWROOM_MAPPING as $mapping) {
            DB::table('showroom_fields_mapping')->where('map_from', '=', $mapping['map_from'])->delete();
        }
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSemiTrailerPullTypeToEavEntityTypeAttributeTable extends Migration
{
    private const ENTITY_TYPE_ATTRIBUTE_SEMI_TRAILER = [
        'entity_type_id' => 7,
        'attribute_id' => 3,
        'sort_order' => 10,
    ];

    private const TRACTOR_HOOKUP_MAPPING = [
        'map_from' => 'tractor_hookup',
        'map_to' => 'tractor_hookup',
        'type' => 'pull_type',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('eav_entity_type_attribute')->insert(self::ENTITY_TYPE_ATTRIBUTE_SEMI_TRAILER);
        DB::table('dealer_incoming_mappings')->insert(self::TRACTOR_HOOKUP_MAPPING);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('eav_entity_type_attribute')->where([
            'entity_type_id' => self::ENTITY_TYPE_ATTRIBUTE_SEMI_TRAILER['entity_type_id'],
            'attribute_id' => self::ENTITY_TYPE_ATTRIBUTE_SEMI_TRAILER['attribute_id'],
        ])->delete();
    }
}

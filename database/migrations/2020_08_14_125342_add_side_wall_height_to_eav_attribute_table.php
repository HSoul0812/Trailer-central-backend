<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSideWallHeightToEavAttributeTable extends Migration
{
    private const EAV_ATTRIBUTE_DATA = [
        'code' => 'side_wall_height',
        'name' => 'Side Wall Height',
        'type' => 'textbox',
        'values' => '',
        'extra_values' => null,
        'description' => 'Side wall height of your trailer in feet (ft).' . PHP_EOL . 'You may enter it in feet and inches (e.g. 1\'6") or feet (e.g. 1.5).',
    ];

    private const EAV_ENTITY_TYPE_DATA = [
        'entity_type_id' => 1,
        'sort_order' => 55
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attributeId = DB::table('eav_attribute')->insertGetId(self::EAV_ATTRIBUTE_DATA);

        DB::table('eav_entity_type_attribute')->insert(array_merge(['attribute_id' => $attributeId], self::EAV_ENTITY_TYPE_DATA));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $attributeId = DB::table('eav_attribute')->select(['attribute_id'])->where(['code' => self::EAV_ATTRIBUTE_DATA['code']])->get();

        DB::table('eav_entity_type_attribute')->where(['attribute_id' => $attributeId])->delete();
        DB::table('eav_attribute')->where(['attribute_id' => $attributeId])->delete();
    }
}

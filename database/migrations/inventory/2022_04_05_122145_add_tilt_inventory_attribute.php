<?php

use Illuminate\Database\Migrations\Migration;

class AddTiltInventoryAttribute extends Migration
{
    private const EAV_ATTRIBUTE_DATA = [
        [
            'attribute_data' => [
                'code' => 'tilt',
                'name' => 'Is TILT',
                'type' => 'select',
                'values' => '0:No,1:Yes',
                'extra_values' => null,
                'default_value' => 0,
                'description' => 'TILT',
            ],
            'attribute_entity' => [
                ['entity_type_id' => 1, 'sort_order' => 20],
                ['entity_type_id' => 7, 'sort_order' => 30],
            ]
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        foreach (self::EAV_ATTRIBUTE_DATA as $attributeData) {
            $attributeId = DB::table('eav_attribute')->insertGetId($attributeData['attribute_data']);

            foreach ($attributeData['attribute_entity'] as $entity) {
                DB::table('eav_entity_type_attribute')->insert(array_merge(['attribute_id' => $attributeId], $entity));
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        foreach (self::EAV_ATTRIBUTE_DATA as $attributeData) {
            $attributeId = DB::table('eav_attribute')
                ->select(['attribute_id'])
                ->where(['code' => $attributeData['attribute_data']['code']])
                ->value('attribute_id');

            DB::table('eav_entity_type_attribute')->where(['attribute_id' => $attributeId])->delete();
            DB::table('eav_attribute')->where(['attribute_id' => $attributeId])->delete();
        }
    }
}

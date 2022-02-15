<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMonthlyRateToEavAttributeTable extends Migration
{
    private const EAV_ATTRIBUTE_DATA = [
        'code' => 'monthly_price',
        'name' => 'Monthly Rate',
        'type' => 'textbox',
        'values' => '',
        'extra_values' => null,
        'description' => 'Monthly Price rate for rentals.',
    ];

    private const EAV_ENTITY_TYPE_DATA = [
        [
            'entity_type_id' => 1,
            'sort_order' => 91
        ],
        [
            'entity_type_id' => 2,
            'sort_order' => 121
        ],
        [
            'entity_type_id' => 3,
            'sort_order' => 27
        ],
        [
            'entity_type_id' => 4,
            'sort_order' => 26
        ],
        [
            'entity_type_id' => 6,
            'sort_order' => 111
        ],
        [
            'entity_type_id' => 8,
            'sort_order' => 43
        ],
        [
            'entity_type_id' => 9,
            'sort_order' => 26
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $attributeId = DB::table('eav_attribute')->insertGetId(self::EAV_ATTRIBUTE_DATA);

        foreach(self::EAV_ENTITY_TYPE_DATA as $eavEntityType) {
            DB::table('eav_entity_type_attribute')->insert(array_merge(['attribute_id' => $attributeId], $eavEntityType));
        }
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

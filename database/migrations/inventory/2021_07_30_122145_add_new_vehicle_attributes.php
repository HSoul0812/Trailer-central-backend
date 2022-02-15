<?php

use Illuminate\Database\Migrations\Migration;

class AddNewVehicleAttributes extends Migration
{
    private const EAV_ATTRIBUTE_DATA = [
        [
            'attribute_data' => [
                'code' => 'body',
                'name' => 'Body',
                'type' => 'select',
                'values' => 'hatchback:Hatchback,sedan:Sedan,muvsuv:MUV/SUV,coupe:Coupe,convertible:Convertible,wagon:Wagon,van:Van,jeep:Jeep',
                'extra_values' => null,
                'description' => 'Vehicle Body',
            ],
            'attribute_entity' => [
                'entity_type_id' => 4,
                'sort_order' => 122
            ]
        ],
        [
            'attribute_data' => [
                'code' => 'doors',
                'name' => 'Doors',
                'type' => 'select',
                'values' => '2:2,4:4',
                'extra_values' => null,
                'description' => 'Vehicle Doors',
            ],
            'attribute_entity' => [
                'entity_type_id' => 4,
                'sort_order' => 123
            ]
        ],
        [
            'attribute_data' => [
                'code' => 'transmission_speed',
                'name' => 'Transmission Speed',
                'type' => 'select',
                'values' => '3:3,4:4,5:5,6:6,7:7,8:8,9:9,10:10',
                'extra_values' => null,
                'description' => 'Vehicle Transmission Speed',
            ],
            'attribute_entity' => [
                'entity_type_id' => 4,
                'sort_order' => 124
            ]
        ],
        [
            'attribute_data' => [
                'code' => 'series',
                'name' => 'Series',
                'type' => 'textbox',
                'values' => '',
                'extra_values' => null,
                'description' => 'Vehicle Series',
            ],
            'attribute_entity' => [
                'entity_type_id' => 4,
                'sort_order' => 125
            ]
        ],
        [
            'attribute_data' => [
                'code' => 'city_mpg',
                'name' => 'City MPG',
                'type' => 'textbox',
                'values' => '',
                'extra_values' => null,
                'description' => 'Vehicle City MPG',
            ],
            'attribute_entity' => [
                'entity_type_id' => 4,
                'sort_order' => 126
            ]
        ],
        [
            'attribute_data' => [
                'code' => 'highway_mpg',
                'name' => 'Highway MPG',
                'type' => 'textbox',
                'values' => '',
                'extra_values' => null,
                'description' => 'Vehicle Highway MPG',
            ],
            'attribute_entity' => [
                'entity_type_id' => 4,
                'sort_order' => 127
            ]
        ]
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach(self::EAV_ATTRIBUTE_DATA as $attributeData) {
            $attributeId = DB::table('eav_attribute')->insertGetId($attributeData['attribute_data']);
            DB::table('eav_entity_type_attribute')->insert(array_merge(['attribute_id' => $attributeId], $attributeData['attribute_entity']));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach(self::EAV_ATTRIBUTE_DATA as $attributeData) {
            $attributeId = DB::table('eav_attribute')->select(['attribute_id'])->where(['code' => $attributeData['attribute_data']['code']])->get();

            DB::table('eav_entity_type_attribute')->where(['attribute_id' => $attributeId])->delete();
            DB::table('eav_attribute')->where(['attribute_id' => $attributeId])->delete();
        }
        
    }
}

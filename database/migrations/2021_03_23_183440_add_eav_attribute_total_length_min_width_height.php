<?php

use App\Models\Inventory\Attribute;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\EntityTypeAttribute;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddEavAttributeTotalLengthMinWidthHeight extends Migration
{
    private const NEW_DIMS_EAV_ATTRIBUTES = [
        [
            'code' => 'overall_length',
            'name' => 'Overall Length',
            'type' => 'textbox',
            'values' => '',
            'extra_values' => NULL,
            'description' => 'Overall length of the vehicle.',
            'default_value' => '',
            'aliases' => ''
        ],
        [
            'code' => 'min_width',
            'name' => 'Min Width',
            'type' => 'textbox',
            'values' => '',
            'extra_values' => NULL,
            'description' => 'Minimum width of the vehicle.',
            'default_value' => '',
            'aliases' => ''
        ],
        [
            'code' => 'min_height',
            'name' => 'Min Height',
            'type' => 'textbox',
            'values' => '',
            'extra_values' => NULL,
            'description' => 'Minimum height of the vehicle.',
            'default_value' => '',
            'aliases' => ''
        ],
    ];

    private const EAV_ENTITY_ATTRIBUTE_MAP = [
        [
            'code' => 'overall_length',
            'sort_order' => -15
        ],
        [
            'code' => 'min_width',
            'sort_order' => -10
        ],
        [
            'code' => 'min_height',
            'sort_order' => -5
        ]
    ];

    private const EAV_ENTITY_TYPE_IDS = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert Dimensions
        $ids = [];
        foreach(self::NEW_DIMS_EAV_ATTRIBUTES as $eavAttribute) {
            $code = $eavAttribute['code'];
            $ids[$code] = DB::table('eav_attribute')->insertGetId($eavAttribute);
        }

        // Get All Entity Types to Update
        foreach(self::EAV_ENTITY_TYPE_IDS as $entityTypeId) {
            foreach(self::EAV_ENTITY_ATTRIBUTE_MAP AS $attrMap) {
                $attrMap['entity_type_id'] = $entityTypeId;
                $attrMap['attribute_id'] = $ids[$attrMap['code']];
                unset($attrMap['code']);

                // Insert Attribute
                DB::table('eav_entity_type_attribute')->insert($attrMap);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Find Attribute ID's
        $attributeIds = [];
        foreach(self::NEW_DIMS_EAV_ATTRIBUTES as $eavAttribute) {
            $attribute = Attribute::where('code', $eavAttribute['code'])->first();
            $attributeIds[] = $attribute->attribute_id;
        }

        // Delete Entity Type Attributes
        foreach($attributeIds as $id) {
            EntityTypeAttribute::where('attribute_id', $id)->delete();
            Attribute::find($id)->delete();
        }
    }
}

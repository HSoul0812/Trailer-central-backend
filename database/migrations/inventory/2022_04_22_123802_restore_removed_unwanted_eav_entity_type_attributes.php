<?php

use App\Models\Inventory\Attribute;

use App\Models\Inventory\EntityType;

use App\Models\Inventory\EntityTypeAttribute;

use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB;

class RestoreRemovedUnwantedEavEntityTypeAttributes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('eav_entity_type_attribute')->insertOrIgnore([
            [
                'entity_type_id' => EntityType::where('name', 'rv')->value('entity_type_id'),
                'attribute_id' => Attribute::where('code', 'livingquarters')->value('attribute_id')
            ],
            [
                'entity_type_id' => EntityType::where('name', 'horsetrailer')->value('entity_type_id'),
                'attribute_id' => Attribute::where('code', 'slideouts')->value('attribute_id')
            ],
            [
                'entity_type_id' => EntityType::where('name', 'horsetrailer')->value('entity_type_id'),
                'attribute_id' => Attribute::where('code', 'shortwall_length')->value('attribute_id')
            ],
            [
                'entity_type_id' => EntityType::where('name', 'trailer')->value('entity_type_id'),
                'attribute_id' => Attribute::where('code', 'sleeping_capacity')->value('attribute_id')
            ],
            [
                'entity_type_id' => EntityType::where('name', 'trailer')->value('entity_type_id'),
                'attribute_id' => Attribute::where('code', 'slideouts')->value('attribute_id')
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        EntityTypeAttribute::where([
            'entity_type_id' => EntityType::where('name', 'rv')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'livingquarters')->value('attribute_id')
        ])->delete();

        EntityTypeAttribute::where([
            'entity_type_id' => EntityType::where('name', 'horsetrailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'slideouts')->value('attribute_id')
        ])->delete();

        EntityTypeAttribute::where([
            'entity_type_id' => EntityType::where('name', 'horsetrailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'shortwall_length')->value('attribute_id')
        ])->delete();

        EntityTypeAttribute::where([
            'entity_type_id' => EntityType::where('name', 'trailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'sleeping_capacity')->value('attribute_id')
        ])->delete();

        EntityTypeAttribute::where([
            'entity_type_id' => EntityType::where('name', 'trailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'slideouts')->value('attribute_id')
        ])->delete();
    }
}

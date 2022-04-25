<?php

use App\Models\Inventory\Attribute;
use App\Models\Inventory\EntityType;
use App\Models\Inventory\EntityTypeAttribute;
use Illuminate\Database\Migrations\Migration;

class RemoveUnwantedEavEntityTypeAttribute extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        EntityTypeAttribute::unguard();

        EntityTypeAttribute::create([
            'entity_type_id' => EntityType::where('name', 'rv')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'livingquarters')->value('attribute_id')
        ]);

        EntityTypeAttribute::create([
            'entity_type_id' => EntityType::where('name', 'horsetrailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'slideouts')->value('attribute_id')
        ]);

        EntityTypeAttribute::create([
            'entity_type_id' => EntityType::where('name', 'horsetrailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'shortwall_length')->value('attribute_id')
        ]);

        EntityTypeAttribute::create([
            'entity_type_id' => EntityType::where('name', 'trailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'sleeping_capacity')->value('attribute_id')
        ]);

        EntityTypeAttribute::create([
            'entity_type_id' => EntityType::where('name', 'trailer')->value('entity_type_id'),
            'attribute_id' => Attribute::where('code', 'slideouts')->value('attribute_id')
        ]);

        EntityTypeAttribute::reguard();
    }
}

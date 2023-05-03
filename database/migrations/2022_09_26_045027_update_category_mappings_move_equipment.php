<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateCategoryMappingsMoveEquipment extends Migration
{
    /**
     * Run the migrations.
     */
    public const CATEGORY_NEW_MAPPINGS = [
        'General Trailers' => [
            ['map_from' => 'Equipment / Flatbed Trailers', 'map_to' => 'equipment;flatbed;deckover;landscape'],
            ['map_from' => 'Utility Trailers', 'map_to' => 'utility;multisport'],
        ],
    ];

    public const CATEGORY_OLD_MAPPINGS = [
        'General Trailers' => [
            ['map_from' => 'Equipment / Flatbed Trailers', 'map_to' => 'flatbed;deckover;landscape'],
            ['map_from' => 'Utility Trailers', 'map_to' => 'utility;equipment;multisport'],
        ],
    ];

    public function up()
    {
        foreach (self::CATEGORY_NEW_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                $category_mapping = CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->first();
                $category_mapping->update(['map_to' => $category['map_to']]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        foreach (self::CATEGORY_OLD_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                $category_mapping = CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->first();
                $category_mapping->update(['map_to' => $category['map_to']]);
            }
        }
    }
}

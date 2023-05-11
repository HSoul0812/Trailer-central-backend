<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateCategoryMappingsRemoveDuplicate extends Migration
{
    public const CATEGORY_NEW_MAPPINGS = [
        'General Trailers' => [
            ['map_from' => 'Equipment / Flatbed Trailers', 'map_to' => 'equipment;flatbed;landscape'],
        ],
        'Travel Trailers' => [
            ['map_from' => 'Travel Trailers', 'map_to' => 'tiny_house;camping_rv;tent-camper;camper_popup;destination_trailer;expandable;camper_aframe;fish_house;rv_other;park_model;camper_teardrop'],
        ],
    ];

    public const OLD_CATEGORY_MAPPINGS = [
        'General Trailers' => [
            ['map_from' => 'Equipment / Flatbed Trailers', 'map_to' => 'equipment;flatbed;deckover;landscape'],
        ],
        'Travel Trailers' => [
            ['map_from' => 'Travel Trailers', 'map_to' => 'tiny_house;ice-fish_houseice_shack;tent-camper;camping_rv;tent-camper;camper_popup;destination_trailer;expandable;camper_aframe;fish_house;rv_other;park_model;camper_teardrop'],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        foreach (self::CATEGORY_NEW_MAPPINGS as $type => $categories) {
            $currentType = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $currentCategory = $currentType->categories()->where('name', $category['map_from'])->first();
                $categoryMapping = CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $currentCategory->id)->first();
                if ($categoryMapping) {
                    $categoryMapping->update(['map_to' => $category['map_to']]);
                } else {
                    CategoryMappings::create([
                        'category_id' => $currentCategory->id,
                        'map_from' => $category['map_from'],
                        'map_to' => $category['map_to'],
                        'type' => 'Inventory',
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        foreach (self::OLD_CATEGORY_MAPPINGS as $type => $categories) {
            $currentType = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $currentCategory = $currentType->categories()->where('name', $category['map_from'])->first();

                CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $currentCategory->id)->update(['map_to' => $category['map_to']]);
            }
        }
    }
}

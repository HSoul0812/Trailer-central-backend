<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateCategoryMappingsTable extends Migration
{
    public const CATEGORY_NEW_MAPPINGS = [
      'Semi Trailers' => [
        ['map_from' => 'Low Boy / Drop Deck', 'map_to' => 'semi_detach;semi_double;semi_lowboy;semi_drop'],
        ['map_from' => 'Dry Van', 'map_to' => 'semi_container;semi_curtainside;semi_drop-van;semi_dryvan'],
      ],
    ];

    public const OLD_CATEGORY_MAPPINGS = [
      'Semi Trailers' => [
        ['map_from' => 'Low Boy / Drop Deck', 'map_to' => 'semi_detach;semi_double;semi_lowboy'],
        ['map_from' => 'Dry Van', 'map_to' => 'semi_container;semi_curtainside;semi_drop;semi_drop-van;semi_dryvan'],
      ],
    ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        foreach (self::CATEGORY_NEW_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                $category_mapping = CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->first();
                if ($category_mapping) {
                    $category_mapping->update(['map_to' => $category['map_to']]);
                } else {
                    CategoryMappings::create([
                      'category_id' => $current_category->id,
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
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();

                CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->update(['map_to' => $category['map_to']]);
            }
        }
    }
}

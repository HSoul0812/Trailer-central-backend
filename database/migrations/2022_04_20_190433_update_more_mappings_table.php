<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateMoreMappingsTable extends Migration
{
    public const CATEGORY_NEW_MAPPINGS = [
      'Semi Trailers' => [
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed;semi_highboy'],
        ['map_from' => 'Low Boy / Drop Deck', 'map_to' => 'semi_detach;semi_double;semi_lowboy'],
        ['map_from' => 'Dry Van', 'map_to' => 'semi_container;semi_curtainside;semi_drop;semi_drop-van;semi_dryvan'],
        ['map_from' => 'Grain', 'map_to' => 'semi_belt;semi_grain-hopper;semi_hopper_trailers'],
        ['map_from' => 'Livestock', 'map_to' => 'semi_horse;semi_livestock'],
        ['map_from' => 'Tank / Bulk', 'map_to' => 'semi_bulk;semi_tanker'],
        ['map_from' => 'Other', 'map_to' => 'semi_btrain;semi_dolley;semi_livefloor;semi_log;semi_other'],
      ],
    ];

    public const OLD_CATEGORY_MAPPINGS = [
      'Semi Trailers' => [
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed;car_racing;equipment'],
        ['map_from' => 'Low Boy / Drop Deck', 'map_to' => 'semi_lowboy;semi_drop'],
        ['map_from' => 'Dry Van', 'map_to' => 'semi_dryvan'],
        ['map_from' => 'Grain', 'map_to' => 'semi_grain-hopper;semi_hopper_trailers'],
        ['map_from' => 'Livestock', 'map_to' => 'semi_livestock'],
        ['map_from' => 'Tank / Bulk', 'map_to' => 'tank_trailer;semi_tanker'],
        ['map_from' => 'Other', 'map_to' => 'semi_other;semi_belt;semi_livefloor'],
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

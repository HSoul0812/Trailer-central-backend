<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateMappingsTable extends Migration
{
    public const CATEGORY_NEW_MAPPINGS = [
      'Equipment Trailers' => [
        ['map_from' => 'Car Haulers / racing', 'map_to' => 'car_racing;deckover;stacker'],
        ['map_from' => 'Utility', 'map_to' => 'utility;equipment;multisport'],
        ['map_from' => 'Dump', 'map_to' => 'dump;dump_bin;rollster'],
        ['map_from' => 'Office / Fiber Optic', 'map_to' => 'office;fiber_splicing'],
        ['map_from' => 'Other', 'map_to' => 'other;tank_trailer'],
      ],
      'Semi Trailers' => [
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed;car_racing;equipment'],
        ['map_from' => 'Livestock', 'map_to' => 'semi_livestock'],
        ['map_from' => 'Tank / Bulk', 'map_to' => 'tank_trailer;semi_tanker'],
        ['map_from' => 'Other Trucks', 'map_to' => 'semitruck_tanker_truck;semitruck_flatbed_truck;semitruck_dump_truck;semitruck_other;semitruck_offroad;semitruck_highway;semitruck_heavy'],
      ],
    ];

    public const OLD_CATEGORY_MAPPINGS = [
      'Equipment Trailers' => [
        ['map_from' => 'Car Haulers / racing', 'map_to' => 'car_racing;deckover'],
        ['map_from' => 'Utility', 'map_to' => 'utility;equipment;multisport;rollster'],
        ['map_from' => 'Dump', 'map_to' => 'dump;dump_bin'],
        ['map_from' => 'Office / Fiber Optic', 'map_to' => 'office'],
        ['map_from' => 'Other', 'map_to' => 'other'],
      ],
      'Semi Trailers' => [
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed'],
        ['map_from' => 'Livestock', 'map_to' => 'semi_livestock;stock_stock-combo'],
        ['map_from' => 'Tank / Bulk', 'map_to' => 'tank_trailer'],
        ['map_from' => 'Other Trucks', 'map_to' => 'semi_tanker;semitruck_tanker_truck;semitruck_flatbed_truck;semitruck_dump_truck;semitruck_other;semitruck_offroad;semitruck_highway;semitruck_heavy'],
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

<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateCategoryMappings extends Migration
{
    public const CATEGORY_NEW_MAPPINGS = [
      'Equipment Trailers' => [
        ['map_from' => 'Tilt', 'map_to' => 'semi_tilt'],
      ],
      'Semi Trailers' => [
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed'],
        ['map_from' => 'Standard Trucks', 'map_to' => 'semitruck_standard'],
        ['map_from' => 'Other', 'map_to' => 'semi_other;semi_belt;semi_livefloor'],
        ['map_from' => 'Other Trucks', 'map_to' => 'semi_tanker;semitruck_tanker_truck;semitruck_flatbed_truck;semitruck_dump_truck;semitruck_other;semitruck_offroad;semitruck_highway;semitruck_heavy'],
      ],
    ];

    public const OLD_CATEGORY_MAPPINGS = [
      'Equipment Trailers' => [
        ['map_from' => 'Tilt', 'map_to' => ''],
      ],
      'Semi Trailers' => [
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed;car_racing;equipment'],
        ['map_from' => 'Standard Trucks', 'map_to' => ''],
        ['map_from' => 'Other', 'map_to' => 'semi_other'],
        ['map_from' => 'Other Trucks', 'map_to' => 'vehicle_truck'],
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

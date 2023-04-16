<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Parts\Category;
use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;

class UpdateCategoryMappings extends Migration
{
    const CATEGORY_NEW_MAPPINGS = [
        'General Trailers' => [
            ['map_from' => 'Vending / Concession Trailers', 'map_to' => 'vending_concession;bbq'],
            ['map_from' => 'Office / Fiber Optic Trailers', 'map_to' => 'office;fiber_splicing;contractor;restroom_shower'],
            ['map_from' => 'Other Trailers', 'map_to' => 'other;tank_trailer;trailer_fuel;'
                .'ice-fish_house;ice_shack;Pressure_Washer;refrigerated;specialty;pipe;trash'],
            ['map_from' => 'Tilt Trailers', 'map_to' => 'semi_tilt;trailer_tilt']
        ],
        'Horse & Livestock' => [
            ['map_from' => 'Stock / Stock Combo Trailers', 'map_to' => 'stock_stock-combo;stock;hay']
        ],
        'Travel Trailers' => [
            ['map_from' => 'Travel Trailers', 'map_to' => 'tiny_house;ice-fish_houseice_shack;tent-camper;'
                .'camping_rv;tent-camper;camper_popup;destination_trailer;expandable;camper_aframe;fish_house;'
                .'rv_other;park_model;camper_teardrop'],
        ],
        'Truck Beds' => [
            ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment;dump_insert;kuv_bodies;platform_bodies;'
                .'saw_bodies;truck_cap;van_bodies']
        ],
    ];

    const OLD_CATEGORY_MAPPINGS = [
        'General Trailers' => [
            ['map_from' => 'Vending / Concession Trailers', 'map_to' => 'vending_concession'],
            ['map_from' => 'Office / Fiber Optic Trailers', 'map_to' => 'office;fiber_splicing'],
            ['map_from' => 'Other Trailers', 'map_to' => 'other;tank_trailer'],
            ['map_from' => 'Tilt Trailers', 'map_to' => 'semi_tilt']
        ],
        'Horse & Livestock' => [
            ['map_from' => 'Stock / Stock Combo Trailers', 'map_to' => 'stock_stock-combo;stock']
        ],
        'Travel Trailers' => [
            ['map_from' => 'Travel Trailers', 'map_to' => 'tiny_house;ice-fish_houseice_shack;tent-camper;'
                .'camping_rv;tent-camper;camper_popup;destination_trailer;expandable'],
        ],
        'Truck Beds' => [
            ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment']
        ],
    ];
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::CATEGORY_NEW_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                $category_mapping = CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->first();
                if ($category_mapping) {
                    $category_mapping->update(['map_to'=> $category['map_to']]);
                } else {
                    CategoryMappings::create([
                        'category_id' => $current_category->id,
                        'map_from' => $category['map_from'],
                        'map_to'   => $category['map_to'],
                        'type'     => 'Inventory'
                    ]);
                }
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
        foreach (self::OLD_CATEGORY_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();

                CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->update(['map_to'=> $category['map_to']]);
            }
        }
    }
}

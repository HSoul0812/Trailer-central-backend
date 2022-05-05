<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDestinationTrailersCategoryMappingsTable extends Migration
{
    const CATEGORY_NEW_MAPPINGS = [
        'Travel Trailers' => [
            ['map_from' => 'Travel', 'map_to' => 'tiny_house;ice-fish_houseice_shack;tent-camper;camping_rv;tent-camper;camper_popup;destination_trailers'],
            ['map_from' => 'Camper / RV', 'map_to' => 'class_a;offroad;class_b;class_bplus;class_c'],
        ]
    ];

    const OLD_CATEGORY_MAPPINGS = [
        'Travel Trailers' => [
            ['map_from' => 'Travel', 'map_to' => 'tiny_house;ice-fish_houseice_shack;tent-camper;camping_rv;tent-camper;camper_popup'],
            ['map_from' => 'Camper / RV', 'map_to' => 'class_a;offroad;class_b;class_c'],
        ]
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

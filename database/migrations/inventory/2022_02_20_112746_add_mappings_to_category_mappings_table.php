<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AddMappingsToCategoryMappingsTable extends Migration
{
    public const CATEGORY_MAPPINGS = [
      'Equipment Trailers' => [
        ['map_from' => 'Cargo (Enclosed)', 'map_to' => 'cargo_enclosed'],
        ['map_from' => 'Flatbed', 'map_to' => 'flatbed;deckover;landscape'],
        ['map_from' => 'Car Haulers / racing', 'map_to' => 'car_racing;deckover'],
        ['map_from' => 'Tow Dollys', 'map_to' => 'tow_dolly'],
        ['map_from' => 'Motorcycle / Cycle', 'map_to' => 'motorcycle'],
        ['map_from' => 'ATV', 'map_to' => 'atv'],
        ['map_from' => 'Watercraft', 'map_to' => 'watercraft;boat_trailer;personal_watercraft'],
        ['map_from' => 'Snowmobile', 'map_to' => 'snowmobile'],
        ['map_from' => 'Utility', 'map_to' => 'utility;equipment;multisport;rollster'],
        ['map_from' => 'Dump', 'map_to' => 'dump;dump_bin'],
        ['map_from' => 'Vending / Concession', 'map_to' => 'vending_concession'],
        ['map_from' => 'Office / Fiber Optic', 'map_to' => 'office'],
        ['map_from' => 'Other', 'map_to' => 'other'],
      ],
      'Horse & Livestock' => [
        ['map_from' => 'Horse Trailers', 'map_to' => 'horse'],
        ['map_from' => 'Livestock Trailers', 'map_to' => 'equip_livestock'],
        ['map_from' => 'Stock / Stock Combo', 'map_to' => 'stock_stock-combo;stock'],
      ],
      'Travel Trailers' => [
        ['map_from' => 'Travel', 'map_to' => 'tiny_house;ice-fish_houseice_shack;tent-camper;camping_rv;tent-camper;camper_popup'],
        ['map_from' => 'Fifth Wheels', 'map_to' => 'fifth_wheel_campers'],
        ['map_from' => 'Toy Haulers', 'map_to' => 'toy'],
        ['map_from' => 'Camper / RV', 'map_to' => 'class_a;offroad;class_b;class_c'],
      ],
      'Truck Beds' => [
        ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment;truck_bodies;truck_boxes;dump_bodies;gooseneck_bodies;service_bodies'],
      ],
      'Semi Trailers' => [
        ['map_from' => 'Low Boy / Drop Deck', 'map_to' => 'semi_lowboy;semi_drop'],
        ['map_from' => 'Dry Van', 'map_to' => 'semi_dryvan'],
        ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed;car_racing;equipment'],
        ['map_from' => 'Grain', 'map_to' => 'semi_grain-hopper;semi_hopper_trailers'],
        ['map_from' => 'Reefer', 'map_to' => 'semi_reefer'],
        ['map_from' => 'Livestock', 'map_to' => 'semi_livestock;stock_stock-combo'],
        ['map_from' => 'Tank / Bulk', 'map_to' => 'tank_trailer'],
        ['map_from' => 'Dump', 'map_to' => 'semi_dump'],
        ['map_from' => 'Other', 'map_to' => 'semi_other'],
        ['map_from' => 'Other Trucks', 'map_to' => 'vehicle_truck'],
      ],
    ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\Parts\CategoryAndTypeSeeder',
        ]);
        foreach (self::CATEGORY_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();

                CategoryMappings::create([
                  'category_id' => $current_category->id,
                  'map_from' => $category['map_from'],
                  'map_to' => $category['map_to'],
                  'type' => 'Inventory',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('category_mappings', function (Blueprint $table) {
            foreach (self::CATEGORY_MAPPINGS as $type => $categories) {
                $current_type = Type::where('name', $type)->first();
                foreach ($categories as $category) {
                    $current_category = $current_type->categories()->where('name', $category['map_from'])->first();

                    CategoryMappings::where('map_from', $category['map_from'])->where('category_id', $current_category->id)->delete();
                }
            }
        });
    }
}

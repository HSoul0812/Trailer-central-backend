<?php

use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEntityTypeIdToListingCategoryMappingsTable extends Migration
{
    public const OLD_LISTING_CATEGORY_MAPPINGS = [
        'Equipment Trailers' => [
            ['map_from' => 'Cargo (Enclosed)', 'map_to' => 'cargo_enclosed'],
            ['map_from' => 'Flatbed', 'map_to' => 'flatbed'],
            ['map_from' => 'Car Haulers / racing', 'map_to' => 'stacker'],
            ['map_from' => 'Tow Dollys', 'map_to' => 'tow_dolly'],
            ['map_from' => 'ATV', 'map_to' => 'atv'],
            ['map_from' => 'Watercraft', 'map_to' => 'watercraft'],
            ['map_from' => 'Snowmobile', 'map_to' => 'snowmobile'],
            ['map_from' => 'Utility', 'map_to' => 'utility'],
            ['map_from' => 'Dump', 'map_to' => 'dump'],
            ['map_from' => 'Vending / Concession', 'map_to' => 'vending_concession'],
            ['map_from' => 'Office / Fiber Optic', 'map_to' => 'office'],
            ['map_from' => 'Other', 'map_to' => 'other'],
        ],
        'Horse & Livestock' => [
            ['map_from' => 'Horse Trailers', 'map_to' => 'horse'],
            ['map_from' => 'Stock / Stock Combo', 'map_to' => 'stock_stock-combo'],
        ],
        'Travel Trailers' => [
            ['map_from' => 'Travel', 'map_to' => 'camping_rv'],
            ['map_from' => 'Fifth Wheels', 'map_to' => 'fifth_wheel_campers'],
            ['map_from' => 'Toy Haulers', 'map_to' => 'toy'],
            ['map_from' => 'Camper / RV', 'map_to' => 'class_a'],
        ],
        'Truck Beds' => [
            ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment'],
        ],
        'Semi Trailers' => [
            ['map_from' => 'Livestock', 'map_to' => 'semi_livestock'],
            ['map_from' => 'Dry Van', 'map_to' => 'semi_dryvan'],
            ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed'],
            ['map_from' => 'Grain', 'map_to' => 'semi_grain-hopper'],
            ['map_from' => 'Reefer', 'map_to' => 'semi_reefer'],
            ['map_from' => 'Tank / Bulk', 'map_to' => 'tank_trailer'],
            ['map_from' => 'Dump', 'map_to' => 'semi_dump'],
            ['map_from' => 'Other', 'map_to' => 'semi_other'],
            ['map_from' => 'Other Trucks', 'map_to' => 'vehicle_truck'],
        ],
    ];

    public const NEW_LISTING_CATEGORY_MAPPINGS = [
        'Equipment Trailers' => [
            ['map_from' => 'Cargo (Enclosed)', 'map_to' => 'cargo_enclosed', 'entity_type_id' => 1],
            ['map_from' => 'Flatbed', 'map_to' => 'flatbed', 'entity_type_id' => 1],
            ['map_from' => 'Car Haulers / racing', 'map_to' => 'stacker', 'entity_type_id' => 1],
            ['map_from' => 'Tow Dollys', 'map_to' => 'tow_dolly', 'entity_type_id' => 1],
            ['map_from' => 'Motorcycle / Cycle', 'map_to' => 'motorcycle', 'entity_type_id' => 1],
            ['map_from' => 'ATV', 'map_to' => 'atv', 'entity_type_id' => 1],
            ['map_from' => 'Watercraft', 'map_to' => 'watercraft', 'entity_type_id' => 1],
            ['map_from' => 'Snowmobile', 'map_to' => 'snowmobile', 'entity_type_id' => 1],
            ['map_from' => 'Utility', 'map_to' => 'utility', 'entity_type_id' => 1],
            ['map_from' => 'Dump', 'map_to' => 'dump', 'entity_type_id' => 1],
            ['map_from' => 'Vending / Concession', 'map_to' => 'vending_concession', 'entity_type_id' => 1],
            ['map_from' => 'Office / Fiber Optic', 'map_to' => 'office', 'entity_type_id' => 1],
            ['map_from' => 'Other', 'map_to' => 'other', 'entity_type_id' => 6],
        ],
        'Horse & Livestock' => [
            ['map_from' => 'Horse Trailers', 'map_to' => 'horse', 'entity_type_id' => 2],
            ['map_from' => 'Stock / Stock Combo', 'map_to' => 'stock_stock-combo', 'entity_type_id' => 1],
        ],
        'Travel Trailers' => [
            ['map_from' => 'Travel', 'map_to' => 'camping_rv', 'entity_type_id' => 3],
            ['map_from' => 'Fifth Wheels', 'map_to' => 'fifth_wheel_campers', 'entity_type_id' => 3],
            ['map_from' => 'Toy Haulers', 'map_to' => 'toy', 'entity_type_id' => 3],
            ['map_from' => 'Camper / RV', 'map_to' => 'class_a', 'entity_type_id' => 3],
        ],
        'Truck Beds' => [
            ['map_from' => 'Truck Beds', 'map_to' => 'bed_equipment', 'entity_type_id' => 1],
        ],
        'Semi Trailers' => [
            ['map_from' => 'Low Boy / Drop Deck', 'map_to' => 'semi_lowboy', 'entity_type_id' => 7],
            ['map_from' => 'Livestock', 'map_to' => 'semi_livestock', 'entity_type_id' => 7],
            ['map_from' => 'Dry Van', 'map_to' => 'semi_dryvan', 'entity_type_id' => 7],
            ['map_from' => 'Flatbed', 'map_to' => 'semi_flatbed', 'entity_type_id' => 7],
            ['map_from' => 'Grain', 'map_to' => 'semi_grain-hopper', 'entity_type_id' => 7],
            ['map_from' => 'Reefer', 'map_to' => 'semi_reefer', 'entity_type_id' => 7],
            ['map_from' => 'Tank / Bulk', 'map_to' => 'tank_trailer', 'entity_type_id' => 7],
            ['map_from' => 'Dump', 'map_to' => 'semi_dump', 'entity_type_id' => 7],
            ['map_from' => 'Other', 'map_to' => 'semi_other', 'entity_type_id' => 7],
            ['map_from' => 'Other Trucks', 'map_to' => 'semitruck_other', 'entity_type_id' => 9],
            ['map_from' => 'Standard Trucks', 'map_to' => 'semitruck_standard', 'entity_type_id' => 9],
        ],
    ];

    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::table('listing_category_mappings')->truncate();

        Schema::table('listing_category_mappings', function (Blueprint $table) {
            $table->dropColumn('category_id');
            $table->dropTimestamps();
            $table->unsignedInteger('type_id');
            $table->unsignedInteger('entity_type_id');
        });

        foreach (self::NEW_LISTING_CATEGORY_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                DB::table('listing_category_mappings')->insert([
                    'type_id' => $current_type->id,
                    'map_from' => $category['map_from'],
                    'map_to' => $category['map_to'],
                    'entity_type_id' => $category['entity_type_id'],
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
        DB::table('listing_category_mappings')->truncate();

        Schema::table('listing_category_mappings', function (Blueprint $table) {
            $table->dropColumn('type_id');
            $table->dropColumn('entity_type_id');
            $table->unsignedInteger('category_id');
            $table->timestamps();
        });

        foreach (self::OLD_LISTING_CATEGORY_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                DB::table('listing_category_mappings')->create([
                    'category_id' => $current_category->id,
                    'map_from' => $category['map_from'],
                    'map_to' => $category['map_to'],
                    'type' => 'Inventory',
                ]);
            }
        }
    }
}

<?php

use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateListingCategoryMappingsTable extends Migration
{
    public const LISTING_CATEGORY_MAPPINGS = [
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

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('listing_category_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('category_id')->index('listing_category_mappings_i_category_id');
            $table->string('map_from');
            $table->string('map_to');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        foreach (self::LISTING_CATEGORY_MAPPINGS as $type => $categories) {
            $current_type = Type::where('name', $type)->first();
            foreach ($categories as $category) {
                $current_category = $current_type->categories()->where('name', $category['map_from'])->first();
                DB::table('listing_category_mappings')->insert([
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
        Schema::dropIfExists('listing_category_mappings');
    }
}

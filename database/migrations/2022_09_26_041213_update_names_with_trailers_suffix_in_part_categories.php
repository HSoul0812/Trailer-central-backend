'<?php

use App\Models\Parts\CategoryMappings;
use App\Models\Parts\ListingCategoryMappings;
use App\Models\Parts\Type;
use Illuminate\Database\Migrations\Migration;

class UpdateNamesWithTrailersSuffixInPartCategories extends Migration
{
    /**
     * Run the migrations.
     */
    public const CATEGORY_NAMINGS = [
        'General Trailers' => [
            ['from' => 'Cargo (Enclosed)', 'to' => 'Cargo (Enclosed) Trailers'],
            ['from' => 'Flatbed', 'to' => 'Equipment / Flatbed Trailers'],
            ['from' => 'Car Haulers / racing', 'to' => 'Car Haulers / racing Trailers'],
            ['from' => 'Tilt', 'to' => 'Tilt Trailers'],
            ['from' => 'Motorcycle / Cycle', 'to' => 'Motorcycle / Cycle Trailers'],
            ['from' => 'ATV', 'to' => 'ATV Trailers'],
            ['from' => 'Watercraft', 'to' => 'Watercraft Trailers'],
            ['from' => 'Snowmobile', 'to' => 'Snowmobile Trailers'],
            ['from' => 'Utility', 'to' => 'Utility Trailers'],
            ['from' => 'Dump', 'to' => 'Dump Trailers'],
            ['from' => 'Vending / Concession', 'to' => 'Vending / Concession Trailers'],
            ['from' => 'Office / Fiber Optic', 'to' => 'Office / Fiber Optic Trailers'],
            ['from' => 'Other', 'to' => 'Other Trailers'],
        ],
        'Horse & Livestock' => [
            ['from' => 'Stock / Stock Combo', 'to' => 'Stock / Stock Combo Trailers'],
        ],
        'Travel Trailers' => [
            ['from' => 'Travel', 'to' => 'Travel Trailers'],
            ['from' => 'Fifth Wheels', 'to' => 'Fifth Wheel Trailers'],
        ],
        'Semi Trailers' => [
            ['from' => 'Low Boy / Drop Deck', 'to' => 'Low Boy / Drop Deck Semi Trailers'],
            ['from' => 'Dry Van', 'to' => 'Dry Van Semi Trailers'],
            ['from' => 'Flatbed', 'to' => 'Flatbed Semi Trailers'],
            ['from' => 'Grain', 'to' => 'Grain Semi Trailers'],
            ['from' => 'Reefer', 'to' => 'Reefer Semi Trailers'],
            ['from' => 'Livestock', 'to' => 'Livestock Semi Trailers'],
            ['from' => 'Tank / Bulk', 'to' => 'Tank / Bulk Semi Trailers'],
            ['from' => 'Dump', 'to' => 'Dump Semi Trailers'],
            ['from' => 'Other', 'to' => 'Other Semi Trailers'],
        ],
    ];

    public function up()
    {
        // Update part_categories
        foreach (self::CATEGORY_NAMINGS as $typeName => $namings) {
            $type = Type::where('name', $typeName)->first();
            foreach ($namings as $naming) {
                $category = $type->categories()->where('name', $naming['from'])->first();
                $category->name = $naming['to'];
                $category->save();
                CategoryMappings::where('category_id', $category->id)->update([
                    'map_from' => $naming['to'],
                ]);
                ListingCategoryMappings::where('type_id', $type->id)->where('map_from', $naming['from'])->update([
                    'map_from' => $naming['to'],
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        foreach (self::CATEGORY_NAMINGS as $typeName => $namings) {
            $type = Type::where('name', $typeName)->first();
            foreach ($namings as $naming) {
                $category = $type->categories()->where('name', $naming['to'])->first();
                $category->name = $naming['from'];
                $category->save();
                CategoryMappings::where('category_id', $category->id)->update([
                    'map_from' => $naming['from'],
                ]);
                ListingCategoryMappings::where('type_id', $type->id)->where('map_from', $naming['to'])->update([
                    'map_from' => $naming['from'],
                ]);
            }
        }
    }
}

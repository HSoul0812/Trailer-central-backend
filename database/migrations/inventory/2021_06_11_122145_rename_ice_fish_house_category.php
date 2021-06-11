<?php

declare(strict_types=1);

use App\Models\Inventory\Category;
use App\Models\Inventory\Inventory;
use Illuminate\Database\Migrations\Migration;

class RenameIceFishHouseCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Ice/Fish House Trailer -> Ice House
            Category::where('category', '=', 'trailer.ice-fish_house')
                ->update([
                    'category' => 'trailer.ice_house',
                    'legacy_category' => 'ice_house',
                    'label' => 'Ice House'
                ]);
            Inventory::where('category', '=', 'ice-fish_house')->update(['category' => 'ice_house']);

            // Fish House -> Ice House
            Category::where('category', '=', 'rv.fish_house')
                ->update([
                    'category' => 'rv.ice_house',
                    'legacy_category' => 'ice_house',
                    'label' => 'Ice House'
                ]);
            Inventory::where('category', '=', 'fish_house')->update(['category' => 'ice_house']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function () {
            // Ice House -> Ice/Fish House Trailer
            Category::where('category', '=', 'trailer.ice_house')
                ->update([
                    'category' => 'trailer.ice-fish_house',
                    'legacy_category' => 'ice-fish_house',
                    'label' => 'Ice/Fish House Trailer'
                ]);
            Inventory::where('category', '=', 'ice_house')->update(['category' => 'ice-fish_house']);

            // Ice House -> Fish House
            Category::where('category', '=', 'rv.ice_house')
                ->update([
                    'category' => 'rv.fish_house',
                    'legacy_category' => 'fish_house',
                    'label' => 'Fish House'
                ]);
            Inventory::where('category', '=', 'ice_house')->update(['category' => 'fish_house']);
        });
    }
}

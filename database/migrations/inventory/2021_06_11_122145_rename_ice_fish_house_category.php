<?php

declare(strict_types=1);

use App\Models\Inventory\Category;
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
                    'label' => 'Ice House'
                ]);

            // Fish House -> Ice House
            Category::where('category', '=', 'rv.fish_house')
                ->update([
                    'category' => 'rv.ice_house',
                    'label' => 'Ice House'
                ]);
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
                    'label' => 'Ice/Fish House Trailer'
                ]);

            // Ice House -> Fish House
            Category::where('category', '=', 'rv.ice_house')
                ->update([
                    'category' => 'rv.fish_house',
                    'label' => 'Fish House'
                ]);
        });
    }
}

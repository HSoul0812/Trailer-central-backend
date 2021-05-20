<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Inventory\InventoryFeatureList;

class AddOneBedroomToFeaturesDisplay extends Migration
{
    private const FLOOR_PLANS_ITEM = ", One Bedroom";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        InventoryFeatureList::where('feature_name', 'Floor Plans')
            ->where('show_in_only', 'rv')
            ->update([
            'available_options' => DB::raw('CONCAT(`available_options`, "' . self::FLOOR_PLANS_ITEM . '")')
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        InventoryFeatureList::where('feature_name', 'Floor Plans')
            ->where('show_in_only', 'rv')
            ->update([
            'available_options' => DB::raw('REPLACE(`available_options`, "' . self::FLOOR_PLANS_ITEM . '", "")')
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use App\Models\Inventory\InventoryImage;
use stdClass as Dealer;

class AlterInventoryImageOverlayGeneratedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('inventory_image', function (Blueprint $table): void {
            if (!Schema::hasColumn('inventory_image', 'overlay_updated_at')) {
                $table->timestamp('overlay_updated_at')
                    ->nullable()
                    ->index('inventory_image_overlay_updated_at_index');

                $table->index(
                    ['inventory_id', 'overlay_updated_at'],
                    'inventory_image_inventory_and_overlay_updated_at_index'
                );
            }
        });

        $dealers = DB::table('dealer')->select('dealer_id')->get();

        $dealers->each(static function (Dealer $dealer): void {
            InventoryImage::query()
                ->join('inventory', 'inventory.inventory_id', '=', 'inventory_image.inventory_id')
                ->where('inventory.dealer_id', $dealer->dealer_id)
                ->whereNotNull('inventory.overlay_enabled')
                ->update(['overlay_updated_at' => now()]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('inventory_image', function (Blueprint $table): void {
            $table->dropIndex('inventory_image_overlay_updated_at_index');
            $table->dropIndex('inventory_image_inventory_and_overlay_updated_at_index');

            $table->dropColumn('overlay_updated_at');
        });
    }
}

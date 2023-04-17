<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use stdClass as Dealer;

class AlterInventoryTableAddOverlayLocked extends Migration
{
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            // mitigation for potential migration interruptions
            if (!Schema::hasColumn('inventory', 'overlay_is_locked')) {
                $table->boolean('overlay_is_locked')
                    ->default(false)
                    ->index()
                    ->after('overlay_enabled');
            }
        });

        $dealers = DB::table('dealer')->select('dealer_id')->get();

        $dealers->each(static function (Dealer $dealer): void {
            $updateInventorySQL = <<<SQL
                UPDATE inventory
                JOIN dealer on inventory.dealer_id = dealer.dealer_id
                SET
                    -- should be locked when the inventory overlay config is different from global dealer config
                    overlay_is_locked = IF (
                        inventory.overlay_enabled IS NOT NULL AND inventory.overlay_enabled != dealer.overlay_enabled,
                        true,
                        false
                    )
                WHERE inventory.dealer_id = :dealer_id
SQL;

            DB::statement($updateInventorySQL, ['dealer_id' => $dealer->dealer_id]);
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('overlay_is_locked');
        });
    }
}

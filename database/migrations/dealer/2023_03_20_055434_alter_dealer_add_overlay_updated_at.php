<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User\User as Dealer;

class AlterDealerAddOverlayUpdatedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dealer', function (Blueprint $table): void {
            $table->timestamp('overlay_updated_at')
                ->nullable()
                ->index('dealer_overlay_updated_at_index')
                ->after('overlay_lower_margin');

            $table->index('overlay_enabled', 'dealer_overlay_enabled_index');
        });

        Dealer::query()
            ->whereNotNull('overlay_enabled')
            ->update(['overlay_updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dealer', function (Blueprint $table) {
            $table->dropIndex('dealer_overlay_updated_at_index');
            $table->dropIndex('dealer_overlay_enabled_index');

            $table->dropColumn('overlay_updated_at');
        });
    }
}

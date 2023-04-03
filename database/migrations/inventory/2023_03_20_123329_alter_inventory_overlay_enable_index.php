<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterInventoryOverlayEnableIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('inventory', function (Blueprint $table): void {
            $table->index('overlay_enabled','inventory_overlay_enabled_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table): void {
            $table->dropIndex('inventory_overlay_enabled_index');
        });
    }
}

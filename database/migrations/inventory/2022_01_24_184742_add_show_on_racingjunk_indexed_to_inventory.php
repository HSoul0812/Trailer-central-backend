<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowOnRacingjunkIndexedToInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('inventory', static function (Blueprint $table) {
            $table->index(['dealer_id', 'show_on_racingjunk', 'status'], 'racingjunk_used_slots_dealer_index');
            $table->index(['show_on_racingjunk', 'status'], 'racingjunk_used_slots_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('inventory', static function (Blueprint $table) {
            $table->dropIndex('racingjunk_used_slots_dealer_index');
            $table->dropIndex('racingjunk_used_slots_index');
        });
    }
}

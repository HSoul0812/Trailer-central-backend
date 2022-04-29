<?php

use App\Models\Inventory\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToStockInInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(Inventory::getTableName(), function (Blueprint $table) {
            $table->index(['stock']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(Inventory::getTableName(), function (Blueprint $table) {
            $table->dropIndex(['stock']);
        });
    }
}

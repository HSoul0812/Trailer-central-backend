<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuoteMainInventoryOptions extends Migration
{
    private const TABLE_NAME = 'dms_unit_sale';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->float('inventory_gvwr', 16)->nullable()->default(0)->after('inventory_weight');
            $table->string('inventory_color', 64)->nullable()->default(null)->after('inventory_gvwr');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->dropColumn([
                'inventory_gvwr',
                'inventory_color',
            ]);
        });
    }
}

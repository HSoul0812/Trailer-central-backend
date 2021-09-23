<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuoteInventoryOptions extends Migration
{
    private const TABLE_NAME = 'dms_quote_inventory';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(self::TABLE_NAME, function (Blueprint $table) {
            $table->string('trailer_stock_number', 50)->nullable()->default(null)->after('vin');
            $table->float('gvwr', 16)->nullable()->default(0)->after('trailer_stock_number');
            $table->string('color', 64)->nullable()->default(null)->after('gvwr');
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
                'trailer_stock_number',
                'gvwr',
                'color',
            ]);
        });
    }
}

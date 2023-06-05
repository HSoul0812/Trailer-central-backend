<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateIsRentalPositionValueInInventoryFilterTable extends Migration
{
    private const TABLE = 'inventory_filter';
    private const ATTRIBUTE = 'is_rental';
    private const FIELD = 'position';
    private const VALUE_UP = 21;
    private const VALUE_DOWN = 'NULL';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table(self::TABLE)->where('attribute', '=', self::ATTRIBUTE)->update([self::FIELD => self::VALUE_UP]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table(self::TABLE)->where('attribute', '=', self::ATTRIBUTE)->update([self::FIELD => self::VALUE_DOWN]);
    }
}

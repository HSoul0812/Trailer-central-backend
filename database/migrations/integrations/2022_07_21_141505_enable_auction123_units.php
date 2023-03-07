<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnableAuction123Units extends Migration
{
    private const TABLE_NAME = 'inventory';

    private const DEALERS = [12064, 3838, 9267, 10627, 9234, 1049, 8467, 7435, 5449, 5534];

    private const PARAMS_ON = [
        'show_on_auction123' => 1
    ];

    private const PARAMS_OFF = [
        'show_on_auction123' => 0
    ];


    private const FILTERS = [
        'active' => 1,
        'status' => 1,
        'is_archived' => 0,
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void {
        DB::transaction(static function () {
            DB::table(self::TABLE_NAME)->whereIn('dealer_id', self::DEALERS)->where(self::FILTERS)->update(self::PARAMS_ON);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void {
        DB::transaction(static function () {
            DB::table(self::TABLE_NAME)->whereIn('dealer_id', self::DEALERS)->where(self::FILTERS)->update(self::PARAMS_OFF);
        });
    }


}

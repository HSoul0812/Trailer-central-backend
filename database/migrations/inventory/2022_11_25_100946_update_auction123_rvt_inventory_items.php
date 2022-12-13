<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAuction123RvtInventoryItems extends Migration
{
    private const INVENTORY_TABLE = 'inventory';
    private const INTEGRATION_DEALER_TABLE = 'integration_dealer';

    private const INTEGRATION_IDS_FIELD = [
        4 => 'show_on_rvt',
        35 => 'show_on_auction123',
        36 => 'show_on_auction123'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (self::INTEGRATION_IDS_FIELD as $integrationId => $field) {
            DB::table(self::INVENTORY_TABLE)
                ->join(self::INTEGRATION_DEALER_TABLE, self::INVENTORY_TABLE . '.dealer_id', '=', self::INTEGRATION_DEALER_TABLE . '.dealer_id')
                ->where(self::INTEGRATION_DEALER_TABLE . '.integration_id', '=', $integrationId)
                ->update([$field => 1]);
        }
    }
}

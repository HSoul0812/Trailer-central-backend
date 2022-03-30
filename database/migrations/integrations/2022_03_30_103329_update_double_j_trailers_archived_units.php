<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\DB;

class UpdateDoubleJTrailersArchivedUnits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::transaction(function () {
            Inventory::where('dealer_id', '=',1099)
                ->where('archived_at', '<', '2021')
                ->update(['status' => inventory::STATUS_SOLD]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

    }
}

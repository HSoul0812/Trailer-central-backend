<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Inventory\Inventory;

class ChangeForestRiverIncToForestRiver extends Migration
{
    const MANUFACTURER_FROM = 'Forest River, Inc.';
    const MANUFACTURER_TO = 'Forest River';
    
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $inventories = Inventory::where('manufacturer', self::MANUFACTURER_FROM)->get();
        
        foreach($inventories as $inventory) {
            $inventory->manufacturer = self::MANUFACTURER_TO;
            $inventory->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('crm_pos_register', function (Blueprint $table) {
            $table->dropColumn('meta');
        });

    }
}

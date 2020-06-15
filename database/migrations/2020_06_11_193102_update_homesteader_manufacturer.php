<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryMfg;

class UpdateHomesteaderManufacturer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get Inventory With Homesteader MFG
        Inventory::where('manufacturer', 'Homesteader')
            ->orWhere('manufacturer', 'Homesteader Inc.')
            ->update(['manufacturer' => 'Homesteader Trailers']);

        // Update Inventory MFG Homesteader Entry
        InventoryMfg::where('name', 'Homesteader')
            ->update(['name' => 'Homesteader Trailers', 'label' => 'Homesteader Trailers']);

        // Delete Inventory MFG Homesteader Inc. Entry
        InventoryMfg::where('name', 'Homesteader Inc.')
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

<?php

use App\Models\Parts\CategoryMappings;
use Illuminate\Database\Migrations\Migration;

class UpdateTruckBedCamperCategory extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        CategoryMappings::where('map_from', 'Truck Bed Campers')->update([
            'map_to' => 'rv.truck_camper;truck_camper',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}

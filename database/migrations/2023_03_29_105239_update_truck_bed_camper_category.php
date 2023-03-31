<?php
use Illuminate\Database\Migrations\Migration;
use App\Models\Parts\CategoryMappings;

class UpdateTruckBedCamperCategory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CategoryMappings::where('map_from', 'Truck Bed Campers')->update([
            'map_to' => 'rv.truck_camper;truck_camper',
        ]);
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
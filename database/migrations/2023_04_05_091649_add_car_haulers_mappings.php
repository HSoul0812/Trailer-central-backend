<?php

use App\Models\Parts\CategoryMappings;
use Illuminate\Database\Migrations\Migration;

class AddCarHaulersMappings extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        CategoryMappings::where('map_from', 'Car Haulers / racing Trailers')->update([
            'map_to' => 'car_racing;deckover;stacker;trailer.car_hauler;car_hauler',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
    }
}

<?php

namespace Database\Seeders;

use App\Models\SysConfig\SysConfig;
use Illuminate\Database\Seeder;

class FilterSeeder extends Seeder
{
    const FILTER_CONFIGS = [
        ['filter/size/length_min', '3'],
        ['filter/size/length_max', '100'],
        ['filter/size/width_min', '4'],
        ['filter/size/width_max', '15'],
        ['filter/size/height_min', '3'],
        ['filter/size/height_max', '15'],
        ['filter/price/equipment_min', '0'],
        ['filter/price/equipment_max', '500000'],
        ['filter/price/travel_min', '0'],
        ['filter/price/travel_max', '3000000'],
        ['filter/price/horse_livestock_min', '0'],
        ['filter/price/horse_livestock_max', '1000000'],
        ['filter/price/semi_trucks_min', '0'],
        ['filter/price/semi_trucks_max', '2000000'],
        ['filter/price/truck_beds_min', '0'],
        ['filter/price/truck_beds_max', '150000'],
        ['filter/gvwr/equipment_min', '0'],
        ['filter/gvwr/equipment_max', '40000'],
        ['filter/gvwr/travel_min', '0'],
        ['filter/gvwr/travel_max', '25000'],
        ['filter/gvwr/horse_livestock_min', '0'],
        ['filter/gvwr/horse_livestock_max', '20000'],
        ['filter/gvwr/semi_trucks_min', '0'],
        ['filter/gvwr/semi_trucks_max', '150000'],
        ['filter/payload_capacity/equipment_min', '0'],
        ['filter/payload_capacity/equipment_max', '35000'],
        ['filter/payload_capacity/travel_min', '0'],
        ['filter/payload_capacity/travel_max', '20000'],
        ['filter/payload_capacity/horse_livestock_min', '0'],
        ['filter/payload_capacity/horse_livestock_max', '15000'],
        ['filter/payload_capacity/semi_trucks_min', '0'],
        ['filter/payload_capacity/semi_trucks_max', '125000'],
        ['filter/payload_capacity/truck_beds', '0'],
        ['filter/payload_capacity/truck_beds', '10000'],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->cleanTable();
        foreach(self::FILTER_CONFIGS as $config) {
            SysConfig::create([
                'key' => $config[0],
                'value' => $config[1]
            ]);
        }
    }

    private function cleanTable() {
        SysConfig::where('key', 'LIKE', 'filter/%')->delete();
    }
}

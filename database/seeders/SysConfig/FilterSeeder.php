<?php

namespace Database\Seeders\SysConfig;

use App\Models\SysConfig\SysConfig;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterSeeder extends Seeder
{
    const FILTER_CONFIGS = [
        ['filter/size/length/min', '3'],
        ['filter/size/length/max', '100'],
        ['filter/size/width/min', '4'],
        ['filter/size/width/max', '15'],
        ['filter/size/height/min', '3'],
        ['filter/size/height/max', '15'],
        ['filter/price/equipment/min', '0'],
        ['filter/price/equipment/max', '500000'],
        ['filter/price/travel/min', '0'],
        ['filter/price/travel/max', '3000000'],
        ['filter/price/horse_livestock/min', '0'],
        ['filter/price/horse_livestock/max', '1000000'],
        ['filter/price/semi_trucks/min', '0'],
        ['filter/price/semi_trucks/max', '2000000'],
        ['filter/price/truck_beds/min', '0'],
        ['filter/price/truck_beds/max', '150000'],
        ['filter/gvwr/equipment/min', '0'],
        ['filter/gvwr/equipment/max', '40000'],
        ['filter/gvwr/travel/min', '0'],
        ['filter/gvwr/travel/max', '25000'],
        ['filter/gvwr/horse_livestock/min', '0'],
        ['filter/gvwr/horse_livestock/max', '20000'],
        ['filter/gvwr/semi_trucks/min', '0'],
        ['filter/gvwr/semi_trucks/max', '150000'],
        ['filter/payload_capacity/equipment/min', '0'],
        ['filter/payload_capacity/equipment/max', '35000'],
        ['filter/payload_capacity/travel/min', '0'],
        ['filter/payload_capacity/travel/max', '20000'],
        ['filter/payload_capacity/horse_livestock/min', '0'],
        ['filter/payload_capacity/horse_livestock/max', '15000'],
        ['filter/payload_capacity/semi_trucks/min', '0'],
        ['filter/payload_capacity/semi_trucks/max', '125000'],
        ['filter/payload_capacity/truck_beds/min', '0'],
        ['filter/payload_capacity/truck_beds/max', '10000'],
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
            DB::table('sys_config')->insert([
                'key' => $config[0],
                'value' => $config[1]
            ]);
        }
    }

    private function cleanTable() {
        DB::table('sys_config')->where('key', 'LIKE', 'filter/%')->delete();
    }
}

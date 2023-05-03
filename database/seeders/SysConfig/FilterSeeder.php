<?php

namespace Database\Seeders\SysConfig;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilterSeeder extends Seeder
{
    public const FILTER_CONFIGS = [
        ['filter/size/length/min', '3'],
        ['filter/size/length/max', '100'],
        ['filter/size/width/min', '4'],
        ['filter/size/width/max', '15'],
        ['filter/size/height/min', '3'],
        ['filter/size/height/max', '15'],
        ['filter/price/1/min', '0'],
        ['filter/price/1/max', '500000'],
        ['filter/price/2/min', '0'],
        ['filter/price/2/max', '1000000'],
        ['filter/price/3/min', '0'],
        ['filter/price/3/max', '3000000'],
        ['filter/price/4/min', '0'],
        ['filter/price/4/max', '2000000'],
        ['filter/price/5/min', '0'],
        ['filter/price/5/max', '150000'],
        ['filter/gvwr/1/min', '0'],
        ['filter/gvwr/1/max', '40000'],
        ['filter/gvwr/2/min', '0'],
        ['filter/gvwr/2/max', '20000'],
        ['filter/gvwr/3/min', '0'],
        ['filter/gvwr/3/max', '25000'],
        ['filter/gvwr/4/min', '0'],
        ['filter/gvwr/4/max', '150000'],
        ['filter/payload_capacity/1/min', '0'],
        ['filter/payload_capacity/1/max', '35000'],
        ['filter/payload_capacity/2/min', '0'],
        ['filter/payload_capacity/2/max', '15000'],
        ['filter/payload_capacity/3/min', '0'],
        ['filter/payload_capacity/3/max', '20000'],
        ['filter/payload_capacity/4/min', '0'],
        ['filter/payload_capacity/4/max', '125000'],
        ['filter/payload_capacity/5/min', '0'],
        ['filter/payload_capacity/5/max', '10000'],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->cleanTable();
        foreach (self::FILTER_CONFIGS as $config) {
            DB::table('sys_configs')->insert([
                'key' => $config[0],
                'value' => $config[1],
            ]);
        }
    }

    private function cleanTable()
    {
        DB::table('sys_configs')->where('key', 'LIKE', 'filter/%')->delete();
    }
}

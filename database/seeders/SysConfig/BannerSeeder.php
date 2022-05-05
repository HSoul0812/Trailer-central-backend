<?php

namespace Database\Seeders\SysConfig;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    const BANNER_CONFIGS = [
        ['banner/lamar/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/lamar/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/carry-on_trailer/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/carry-on_trailer/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/haulmark/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/haulmark/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/wells_cargo/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/wells_cargo/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/american_hauler/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/american_hauler/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/big_tex_trailers/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/big_tex_trailers/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/pj_trailers/desktop', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
        ['banner/pj_trailers/mobile', 'https://s3.amazonaws.com/crm-trailercentral-dev/new-cargo-enclosed'],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->cleanTable();
        foreach(self::BANNER_CONFIGS as $config) {
            DB::table('sys_configs')->insert([
                'key' => $config[0],
                'value' => $config[1]
            ]);
        }
    }

    private function cleanTable() {
        DB::table('sys_configs')->where('key', 'LIKE', 'banner/%')->delete();
    }
}

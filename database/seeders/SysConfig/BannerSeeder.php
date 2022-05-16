<?php

namespace Database\Seeders\SysConfig;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    const BANNER_CONFIGS = [
        ['banner/lamar trailers/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-LamarBanners-v1_Desktop-20220505-113050.png'],
        ['banner/lamar trailers/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-LamarBanners-v1_Mobile-20220505-113050.png'],
        ['banner/carry-on/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-CarryOnBanners-v1_Desktop-20220505-113414.png'],
        ['banner/carry-on/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-CarryOnBanners-v1_Mobile-20220505-113414.png'],
        ['banner/haulmark/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-HaulmarkBanners-v1_Desktop-20220505-115252.png'],
        ['banner/haulmark/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-HaulmarkBanners-v1_Mobile-20220505-115252.png'],
        ['banner/wells cargo/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-WellsCargoBanners-v1_Desktop-20220505-133430.png'],
        ['banner/wells cargo/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-WellsCargoBanners-v1_Mobile-20220505-133430.png'],
        ['banner/american hauler/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-AmericanHaulerBanners-v1_Desktop-20220505-123526.png'],
        ['banner/american hauler/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-AmericanHaulerBanners-v1_Mobile-20220505-123526.png'],
        ['banner/big tex trailers/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-BigTexBanners-v1_Desktop-20220505-124600.png'],
        ['banner/big tex trailers/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-BigTexBanners-v1_Mobile-20220505-124600.png'],
        ['banner/pj trailers/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-PJBanners-v1_Desktop-20220505-125942.png'],
        ['banner/pj trailers/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-PJBanners-v1_Mobile-20220505-125942.png'],
        ['banner/norstar/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-NorstarBanners-v1_Desktop-20220505-132316.png'],
        ['banner/norstar/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-NorstarBanners-v1_Mobile-20220505-132316.png'],
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

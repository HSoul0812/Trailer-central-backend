<?php

namespace Database\Seeders\SysConfig;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    public const BANNER_CONFIGS = [
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
        ['banner/bwise/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-BWiseBanners-v1_Desktop-20220505-131302.png'],
        ['banner/bwise/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-BWiseBanners-v1_Mobile-20220505-131302.png'],
        ['banner/h and h trailer/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-HHBanners-v1_Desktop-20220510-150132.png'],
        ['banner/h and h trailer/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-HHBanners-v1_Mobile-20220510-150132.png'],
        ['banner/sure-trac/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-SureTracBanners-v1_Desktop-20220510-144334.png'],
        ['banner/sure-trac/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-SureTracBanners-v1_Mobile-20220510-144334.png'],
        ['banner/cam superline/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-SuperlineBanners-v1_Desktop-20220510-151114.png'],
        ['banner/cam superline/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-SuperlineBanners-v1_Mobile-20220510-151114.png'],
        ['banner/trailerman trailers inc./desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-TrailermanBanners-v1_Desktop-20220510-151711.png'],
        ['banner/trailerman trailers inc./mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-TrailermanBanners-v1_Mobile-20220510-151711.png'],
        ['banner/midsota/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-MidsotaBanners-v1_Desktop-20220510-152906.png'],
        ['banner/midsota/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-MidsotaBanners-v1_Mobile-20220510-152906.png'],
        ['banner/diamond c trailers/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-DiamondCBanners_v1_Desktop.png'],
        ['banner/diamond c trailers/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-DiamondCBanners_v1_Mobile.png'],
        ['banner/trailerman/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-TrailermanBanners-v1_Desktop-20220510-151711.png'],
        ['banner/trailerman/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-TrailermanBanners-v1_Mobile-20220510-151711.png'],
        ['banner/iron bull/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-NorstarBanners-v1_Desktop-20220505-132316.png'],
        ['banner/iron bull/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-NorstarBanners-v1_Mobile-20220505-132316.png'],
        ['banner/iron bull trailers/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-NorstarBanners-v1_Desktop-20220505-132316.png'],
        ['banner/iron bull trailers/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-NorstarBanners-v1_Mobile-20220505-132316.png'],
        ['banner/h&h trailers/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-HHBanners-v1_Desktop-20220510-150132.png'],
        ['banner/h&h trailers/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-HHBanners-v1_Mobile-20220510-150132.png'],
        ['banner/aluma/desktop', 'https://trailertrader.s3.amazonaws.com/banners/aluma-desktop.png'],
        ['banner/aluma/mobile', 'https://trailertrader.s3.amazonaws.com/banners/aluma-mobile.png'],
        ['banner/load trail/desktop', 'https://trailertrader.s3.amazonaws.com/banners/TC-load-trail-desktop.png'],
        ['banner/load trail/mobile', 'https://trailertrader.s3.amazonaws.com/banners/TC-load-trail-mobile.png'],
    ];

    /**
     * Run the database seeds.
     */
    public function run()
    {
        $this->cleanTable();
        foreach (self::BANNER_CONFIGS as $config) {
            DB::table('sys_configs')->insert([
                'key' => $config[0],
                'value' => $config[1],
            ]);
        }
    }

    private function cleanTable()
    {
        DB::table('sys_configs')->where('key', 'LIKE', 'banner/%')->delete();
    }
}

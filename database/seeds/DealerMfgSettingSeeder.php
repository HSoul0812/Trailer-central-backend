<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\CRM\Dealer\DealerMfgSetting;

class DealerMfgSettingSeeder extends Seeder
{
    /**
     * Move inventory manufacturer vendor setting from inventory_mfg to dealer_mfg_setting
     * because the setting is specific to dealers
     * 
     * @return void
     */
    public function run()
    {
        $mfgVendorSettings = DB::table('inventory_mfg AS im')
            ->selectRaw('v.dealer_id, im.id as mfg_id, v.id as vendor_id')
            ->leftJoin('qb_vendors AS v', 'v.id', '=', 'im.vendor_id')
            ->whereNotNull('v.id')
            ->get();
        foreach ($mfgVendorSettings as $setting) {
            DealerMfgSetting::insert([
                'dealer_id' => $setting->dealer_id,
                'inventory_mfg_id' => $setting->mfg_id,
                'vendor_id' => $setting->vendor_id
            ]);
        }
    }
}

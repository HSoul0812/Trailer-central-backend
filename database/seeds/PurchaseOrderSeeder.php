<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrder;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * To add a unique validation to user_defined_id in dms_purchase_order table,
     * Remove all the duplicated user_defined_id
     * 
     * @return void
     */
    public function run()
    {
        $duplicatedPoNumbers = DB::table('dms_purchase_order')
            ->selectRaw('user_defined_id, GROUP_CONCAT(id) AS ids')
            ->groupBy('user_defined_id', 'dealer_id')
            ->havingRaw('COUNT(id) > ?', [1])
            ->get();
        foreach ($duplicatedPoNumbers as $index => $duplicatedPoNumber) {
            $poIds = explode(',', $duplicatedPoNumber->ids);
            foreach ($poIds as $index => $poId) {
                if ($index > 0) {
                    $purchaseOrder = PurchaseOrder::find($poId);
                    $purchaseOrder->user_defined_id = $duplicatedPoNumber->user_defined_id . ' - ' . $index;
                    $purchaseOrder->save();
                }
            }
        }
    }
}

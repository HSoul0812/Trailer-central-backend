<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use App\Models\CRM\Dms\PurchaseOrder\PurchaseOrder;

class RemoveDuplicatedPoNum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->removeDuplicatedPoNumbers();

        Schema::table('dms_purchase_order', function (Blueprint $table) {
            $table->unique(['dealer_id', 'user_defined_id'], 'PO_NUM_UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dms_purchase_order', function (Blueprint $table) {
            $table->dropUnique('PO_NUM_UNIQUE');
        });
    }

    /**
     * To add a unique validation to user_defined_id in dms_purchase_order table,
     * Remove all the duplicated user_defined_id
     */
    private function removeDuplicatedPoNumbers(): void
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

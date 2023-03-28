<?php

namespace App\Domains\DealerExports\BackOffice;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class FloorplanPaymentsExportAction
 *
 * @package App\Domains\DealerExports\BackOffice
 */
class FloorplanPaymentsExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'floorplan_payments';

    public function getQuery()
    {
        return DB::table('inventory')
            ->select([
                'inventory.inventory_id',
                'inventory.fp_committed',
                'inventory.status',
                'inventory.title',
                'inventory.vin',
                'inventory.manufacturer',
                'inventory.cost_of_unit',
                'inventory.fp_balance',
                'inventory.fp_interest_paid',
                'inventory.fp_vendor',
                DB::raw('vendors.name as fp_vendor_name'),
                DB::raw('fp_payments.id as fp_payment_id'),
                DB::raw('fp_payments.type as fp_type'),
                DB::raw('fp_payments.amount as fp_amount'),
                DB::raw('fp_payments.payment_type as fp_payment_type'),
                DB::raw('fp_payments.check_number as fp_check_number'),
                DB::raw('fp_payments.created_at as fp_payment_date'),
            ])
            ->join('qb_vendors as vendors', 'vendors.id', '=', 'inventory.fp_vendor')
            ->join('inventory_floor_plan_payment as fp_payments', 'fp_payments.inventory_id', '=', 'inventory.inventory_id')
            ->where([
                ['is_floorplan_bill', '=', 1],
                ['active', '=', 1],
                ['fp_vendor', '>', 0],
                ['true_cost', '>', 0],
                ['fp_balance', '>', 0]
            ])
            ->whereNotNull('bill_id')
            ->whereNotNull('status')
            ->where('inventory.dealer_id', $this->dealer->dealer_id);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'inventory_id' => 'Inventory Identifier',
                'fp_committed' => 'Floorplanned Date',
                'status' => 'Status',
                'title' => 'Title',
                'vin' => 'Vin',
                'manufacturer' => 'Make',
                'cost_of_unit' => 'Cost',
                'fp_balance' => 'Balance Remaining',
                'fp_interest_paid' => 'Interest Paid',
                'fp_vendor' => 'Floorplan Vendor Identifier',
                'fp_vendor_name' => 'Floorplan Vendor Name',
                'fp_type' => 'Floorplan Type',
                'fp_amount' => 'Floorplan Amount',
                'fp_check_number' => 'Floorplan Check Number',
                'fp_payment_date' => 'Floorplan Payment Date',
                'fp_payment_id' => 'Floorplan Payment Identifier',
                'fp_payment_type' => 'Floorplan Payment Type',
            ])
            ->export();
    }
}

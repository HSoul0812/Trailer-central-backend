<?php

namespace App\Domains\DealerExports\BackOffice;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class PurchaseOrdersExportAction
 *
 * @package App\Domains\DealerExports\BackOffice
 */
class PurchaseOrdersExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'purchase_orders';

    public function getQuery()
    {
        return (DB::table('dms_purchase_order as dpo')
            ->select([
                'dpo.id',
                'dpo.vendor_id',
                'dpo.dealer_id',
                'dpo.dealer_location_id',
                'dpo.user_defined_id',
                'dpo.date_opened',
                'dpo.status',
                'dpo.order_placed',
                'dpo.shipping_tracking',
                'dpo.shipping_date',
                'dpo.paid_by',
                'dpo.invoice_number',
                'dpo.check_number',
                'dpo.memo',
                'dpo.total',
                DB::raw('qb_vendors.name as vendor_name'),
                DB::raw("GROUP_CONCAT(parts.sku separator ' ') as parts_stock"),
                DB::raw('dealer_location.name as dealer_location_name'),
                DB::raw('dms_purchase_order_parts.part_id as dms_purchase_order_parts_part_id'),
                DB::raw('parts.title as parts_title'),
                DB::raw('dms_purchase_order_parts.act_cost as dms_purchase_order_parts_act_cost'),
                DB::raw('dms_purchase_order_parts.qty as dms_purchase_order_parts_qty'),
                DB::raw('dms_purchase_order_parts.qty * dms_purchase_order_parts.act_cost as dms_purchase_order_parts_parts_price'),
            ])
            ->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'dpo.vendor_id')
            ->leftJoin('dms_purchase_order_parts', 'dms_purchase_order_parts.purchase_order_id', '=', 'dpo.id')
            ->leftJoin('parts_v1 as parts', 'dms_purchase_order_parts.part_id', '=', 'parts.id')
            ->leftJoin('dms_part_item', 'dms_part_item.po_id', '=', 'dpo.id')
            ->leftJoin('dealer_location', 'dealer_location.dealer_location_id', '=', 'dpo.dealer_location_id')
            ->where('dpo.dealer_id', $this->dealer->dealer_id)
            ->groupBy('dpo.id'));
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'user_defined_id' => 'PO #',
                'date_opened' => 'Created Date',
                'status' => 'Status',
                'vendor_id' => 'Vendor Identifier',
                'vendor_name' => 'Vendor Name',
                'shipping_tracking' => 'Tracking #',
                'shipping_date' => 'Ordered Date',
                'total' => 'Amount',
                'paid_by' => 'Paid By',
                'invoice_number' => 'Invoice #',
                'check_number' => 'Check #',
                'memo' => 'Memo',
                'dealer_location_id' => 'Dealer Location Identifier',
                'dealer_location_name' => 'Dealer Location',
                'dms_purchase_order_parts_part_id' => 'Part #',
                'parts_title' => 'Part Title',
                'dms_purchase_order_parts_act_cost' => 'Part Actual Cost',
                'dms_purchase_order_parts_qty' => 'Part Quantity',
                'dms_purchase_order_parts_parts_price' => 'Part Total',
            ])
            ->export();
    }
}

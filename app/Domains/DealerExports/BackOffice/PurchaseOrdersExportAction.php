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
        return DB::table('dms_purchase_order')
            ->selectRaw("dms_purchase_order.*, dms_part_item.repair_order_id, qb_vendors.name as vendor_name, GROUP_CONCAT(parts.sku separator ' ') as parts_stock")
            ->leftJoin('qb_vendors', 'qb_vendors.id', '=', 'dms_purchase_order.vendor_id')
            ->leftJoin('dms_purchase_order_parts', 'dms_purchase_order_parts.purchase_order_id', '=', 'dms_purchase_order.id')
            ->leftJoin('parts_v1 as parts', 'dms_purchase_order_parts.part_id', '=', 'parts.id')
            ->leftJoin('dms_part_item', 'dms_part_item.po_id', '=', 'dms_purchase_order.id')
            ->where('dms_purchase_order.dealer_id', $this->dealer->dealer_id)
            ->groupBy('dms_purchase_order.id');
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
                'shipping_date' => 'Ordered Date',
                'status' => 'Status',
                'vendor_id' => 'Vendor Identifier',
                'vendor_name' => 'Vendor Name',
                'total' => 'Amount',
                'invoice_number' => 'Invoice #',
            ])
            ->export();
    }
}

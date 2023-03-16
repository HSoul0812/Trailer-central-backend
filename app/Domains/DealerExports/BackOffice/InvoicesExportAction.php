<?php

namespace App\Domains\DealerExports\BackOffice;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\CRM\Account\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Class InvoiceExportAction
 *
 * @package App\Domains\DealerExports\BackOffice
 */
class InvoicesExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'invoices';

    public function getQuery()
    {
        return DB::query()
            ->select([
                'i.*',
                DB::raw('c.display_name as customer_name'),
                't.name as sales_term_name',
                'pm.name',
                DB::raw('us.title as quote_title'),
                'us.is_po as us_is_po',
                'c.home_phone',
                'c.work_phone',
                'c.cell_phone',
                'cw.display_name as warranty_customer',
                DB::raw("concat(coalesce(us.inventory_vin, ''), ' ', coalesce(ius.vin, ''), ' ', coalesce(iro.vin, '')) as vin"),
                DB::raw('COALESCE(sum(p.amount), 0) as amount_received'),
                DB::raw('pm.name as payment_method'),
                'p.payment_method_id',
                'outlet.register_name',
                'p.register_id',
                DB::raw('(i.total - coalesce(sum(p.amount), 0)) as remain'),
                'qii.item_id',
                'qii.qty as item_qty',
                'qii.description as item_desc',
                'qii.unit_price as item_rate',
                DB::raw('(COALESCE(qii.qty, 0) * COALESCE(qii.unit_price, 0)) as item_amount'),
                'qi.name as item_name',
                'qi.type as item_type',
                DB::raw("if(i.unit_sale_id is null, '', concat('/bill-of-sale/edit/', i.unit_sale_id)) as link"),
                'i.unit_sale_id',
                DB::raw('us.title as unit_sale_title'),
                'i.repair_order_id',
                DB::raw('ro.user_defined_id as repair_order_number'),
            ])
            ->from('qb_invoices as i')
            ->leftJoin('dms_customer as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('qb_terms as t', 't.id', '=', 'i.sales_term_id')
            ->leftJoin('qb_payment as p', 'p.invoice_id', '=', 'i.id')
            ->leftJoin('qb_payment_methods as pm', 'pm.id', '=', 'p.payment_method_id')
            ->leftJoin('crm_pos_register as register', 'register.id', '=', 'p.register_id')
            ->leftJoin('crm_pos_outlet as outlet', 'outlet.id', '=', 'register.outlet_id')
            ->leftJoin('qb_invoice_items as qii', 'qii.invoice_id', '=', 'p.invoice_id')
            ->leftJoin('qb_items as qi', 'qi.id', '=', 'qii.item_id')
            ->leftJoin('dms_unit_sale as us', 'us.id', '=', 'i.unit_sale_id')
            ->leftJoin('dms_repair_order as ro', 'ro.id', '=', 'i.repair_order_id')
            ->leftJoin('dms_customer as cw', 'cw.id', '=', 'ro.warranty_customer_id')
            ->leftJoin('inventory as ius', 'ius.inventory_id', '=', 'us.inventory_id')
            ->leftJoin('inventory as iro', 'iro.inventory_id', '=', 'ro.inventory_id')
            ->where('i.dealer_id', $this->dealer->dealer_id)
            ->groupBy('i.id');
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'doc_num' => 'Invoice Number',
                'qb_doc_num' => 'QB Invoice Number',
                'invoice_date' => 'Invoice Date',
                'due_date' => 'Invoice Due Date',
                'vin' => 'Invoice VIN',
                'customer_id' => 'Customer Identifier',
                'customer_name' => 'Customer Name',
                'sales_term_id' => 'Terms Identifier',
                'sales_term_name' => 'Terms',
                'payment_method_id' => 'Payment Method Identifier',
                'payment_method' => 'Payment Method',
                'register_id' => 'Register Identifier',
                'register_name' => 'Register',
                'item_id' => 'Item Identifier',
                'item_desc' => 'Item Description',
                'item_name' => 'Item Product/Service Name',
                'item_type' => 'Item Type',
                'item_qty' => 'Item Quantity',
                'item_rate' => 'Item Rate',
                'item_amount' => 'Item Amount',
                'shipping' => 'Invoice Shipping',
                'total' => 'Invoice Total',
                'remain' => 'Invoice Remaining Balance',
                'memo' => 'Invoice Notes',
                'warranty_customer' => 'Invoice Warranty Customer',
                'repair_order_id' => 'Repair Order Identifier',
                'repair_order_number' => 'RO #',
                'unit_sale_id' => 'Quote/Deal Identifier',
                'unit_sale_title' => 'Quote/Deal Title',
            ])
            ->export();
    }
}

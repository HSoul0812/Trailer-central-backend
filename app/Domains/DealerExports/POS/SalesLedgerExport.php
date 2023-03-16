<?php

namespace App\Domains\DealerExports\POS;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class SalesLedgerExport
 *
 * @package App\Domains\DealerExports\POS
 */
class SalesLedgerExport extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'pos_sales';

    public function getQuery()
    {
        // return DB::table('qb_payment')
        //     ->selectRaw("qb_payment.id, qb_payment.amount AS total, qb_payment.created_at AS date, c.display_name, c.id AS customer_id, sr.receipt_path,
        //     pm.name AS payment_method_name, pm.type AS payment_method_type, COALESCE(groupedRefund.totalOfRefundAmount, 0) AS totalOfRefundAmount,
        //     qb_payment.related_payment_intent AS payment_intent, i.id as invoice_id, qb_payment.id AS paymentId, '' AS sales_person,
        //     i.total as saleTotal, i.doc_num AS reference_number, GROUP_CONCAT(parts_v1.sku) as parts_sku, GROUP_CONCAT(parts_v1.title) as parts_title")
            // ->leftJoin(
            //     DB::raw("(SELECT refund.tb_primary_id, SUM(refund.amount) AS totalOfRefundAmount
            //         FROM dealer_refunds AS refund
            //         LEFT JOIN dealer_sales_receipt ON dealer_sales_receipt.tb_primary_id = refund.id
            //         WHERE refund.tb_name='qb_payment' AND dealer_sales_receipt.tb_name = 'dealer_refunds' AND refund.dealer_id =" . $this->dealer->dealer_id .
            //         " GROUP BY refund.tb_primary_id) as groupedRefund"),
            //     'groupedRefund.tb_primary_id',
            //     '=',
            //     'qb_payment.id'
            // )
        //     ->leftJoin('crm_pos_register as register', 'register.id', '=', 'qb_payment.register_id')
        //     ->leftJoin('qb_invoices as i', 'i.id', '=', 'qb_payment.invoice_id')
        //     ->leftJoin('qb_invoice_items', 'qb_invoice_items.invoice_id', '=', 'qb_payment.invoice_id')
        //     ->leftJoin('qb_items', function ($join) {
        //         $join->on('qb_invoice_items.item_id', '=', 'qb_items.id')
        //             ->where('qb_items.type', 'part');
        //     })
        //     ->leftJoin('parts_v1', 'qb_items.item_primary_id', '=', 'parts_v1.id')
        //     ->leftJoin('dms_customer as c', 'c.id', '=', 'i.customer_id')
        //     ->leftJoin('dealer_sales_receipt as sr', function ($join) {
        //         $join->on('sr.tb_primary_id', '=', 'qb_payment.id')
        //             ->where('sr.tb_name', 'qb_payment');
        //     })
        //     ->leftJoin('qb_payment_methods as pm', 'pm.id', '=', 'qb_payment.payment_method_id')
        //     ->where('qb_payment.dealer_id', $this->dealer->dealer_id)
        //     ->groupBy('qb_payment.id');


        return DB::table('crm_pos_sales as s')
            ->selectRaw("s.id, s.total, s.created_at AS date, s.discount as sale_discount, c.display_name, sr.receipt_path, groupedRefund.refund_receipts,
            pm.name AS payment_method_name, pm.type AS payment_method_type, COALESCE(groupedRefund.totalOfRefundAmount, 0) AS totalOfRefundAmount,
            s.related_payment_intent AS payment_intent, s.id as pos_sale_id, CONCAT(sp.first_name, ' ', sp.last_name, ' (', sp.email, ')') AS sales_person, sp.id as sales_person_id,
            s.total as saleTotal, 'pos' as type, s.amount_received as amountReceived, s.id AS refNum, qb_items.qty_on_hand as part_qty, sales_products.subtotal as part_total,
            qb_items.name as sales_product_name, qb_items.description as sales_product_name, qb_items.unit_price as sales_products_price, sales_products.subtotal as parts_total,
            sales_products.qty as parts_qty, s.po_no as sales_po_no, s.subTotal as sales_subtotal, s.total as sales_total
             ")
            ->leftJoin(
                DB::raw("(SELECT refund.tb_primary_id, SUM(refund.amount) AS totalOfRefundAmount, GROUP_CONCAT(dealer_sales_receipt.receipt_path) AS refund_receipts
                        FROM dealer_refunds AS refund
                        LEFT JOIN dealer_sales_receipt ON dealer_sales_receipt.tb_primary_id = refund.id
                        WHERE refund.tb_name='crm_pos_sales' AND dealer_sales_receipt.tb_name = 'dealer_refunds' AND refund.dealer_id=" . $this->dealer->dealer_id .
                    " GROUP BY refund.tb_primary_id) as groupedRefund"),
                'groupedRefund.tb_primary_id',
                '=',
                's.id'
            )
            ->leftJoin('crm_pos_register as register', 'register.id', '=', 's.register_id')
            ->leftJoin('crm_pos_sale_products as sales_products', 'sales_products.sale_id', '=', 's.id')
            ->leftJoin('qb_invoices as i', 'i.id', '=', 's.invoice_id')
            ->leftJoin('qb_invoice_items', 'qb_invoice_items.invoice_id', '=', 's.invoice_id')
            ->leftJoin('qb_items', function ($join) {
                $join->on('sales_products.item_id', '=', 'qb_items.id')
                    ->where('qb_items.type', 'part');
            })
            ->leftJoin('parts_v1 as parts', 'qb_items.item_primary_id', '=', 'parts.id')
            ->leftJoin('dms_customer as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('crm_sales_person as sp', 'sp.id', '=', 's.sales_person_id')
            ->leftJoin('dealer_sales_receipt as sr', function ($join) {
                $join->on('sr.tb_primary_id', '=', 's.id')
                    ->where('sr.tb_name', DB::raw("'crm_pos_sales'"));
            })
            ->leftJoin('qb_payment_methods as pm', 'pm.id', '=', 's.payment_method_id')
            ->leftJoin('crm_pos_outlet', 'register.outlet_id', '=', 'crm_pos_outlet.id')
            ->where('crm_pos_outlet.dealer_id', $this->dealer->dealer_id)
            ->groupBy('s.id');
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'reference_number' => 'Reference Number',
                'sales_po_no' => 'PO #',
                'customer_id' => 'Customer Identifier',
                'display_name' => 'Customer Name',
                'sales_person_id' => 'Sales Person Identifier',
                'sales_person' => 'Sales Person',
                'saleTotal' => 'Sales Total',
                'refund_amount' => 'Refund Amount',
                'date' => 'Date',
                'payment_method_name' => 'Payment Method',
                'receipt_path' => 'Receipt',
                'part_id' => 'Part Identifier',
                'sales_product_name' => 'Parts Title',
                'sales_product_description' => 'Parts Description',
                'sales_products_price' => 'Parts Price',
                'parts_total' => 'Parts Total',
                'parts_qty' => 'Parts Qty',
                'parts_total_tax' => 'Parts Total Tax',
                'sales_subtotal' => 'Sales Subtotal',
            ])
            ->export();
    }
}




/*
Reference # Done
Customer Identifier Done
Customer Name Done
Sales Person Name Done
Parts:
    Identifier
    Title
    Quantity
    Unit Price
    Total
Misc Parts:
    Title
    Dealer Cost
    Selling Price
    Quantity
    Subtotal
    Taxable
Misc Labor:
    Labor Code
    Actual Hours
    Paid Hours
    Billed Hours
    Technician Identifier
    Technician Name
    Cost to Dealer
    Quantity
    Selling Price
    SubTotal
    Notes
Fee:
    Identifier
    Title
    Price
    Cost
    Quantity
Shipping Price Done
Discount
Payment Identifier Done
Payment Method Done
Memo
Tax on Shipping
Subtotal
Tax
Total
Refund Amount
Date Done

*/

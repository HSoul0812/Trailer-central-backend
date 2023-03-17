<?php

namespace App\Domains\DealerExports\POS;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class SalesLedgerOtherExport
 *
 * @package App\Domains\DealerExports\POS
 */
class SalesLedgerOtherExport extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'pos_sales_other';

    public function getQuery()
    {
        return DB::table('qb_payment')
            ->selectRaw("qb_payment.id, qb_payment.amount AS total, qb_payment.created_at AS date, c.display_name, c.id AS customer_id, sr.receipt_path,
            pm.name AS payment_method_name, pm.type AS payment_method_type, COALESCE(groupedRefund.totalOfRefundAmount, 0) AS totalOfRefundAmount,
            qb_payment.related_payment_intent AS payment_intent, i.id as invoice_id, qb_payment.id AS payment_id, '' AS sales_person, '' as sales_person_id,
            i.total as sale_total, i.doc_num AS reference_number, GROUP_CONCAT(parts_v1.sku) as parts_sku, GROUP_CONCAT(parts_v1.title) as parts_title, 'other' as type,
            qb_invoice_items.qty as parts_qty, (qb_invoice_items.qty * qb_invoice_items.unit_price) as parts_total, i.total_tax as sales_total_tax,
            qb_invoice_items.taxes_amount as parts_tax, qb_invoice_items.unit_price as parts_price")
            ->leftJoin(
                DB::raw("(SELECT refund.tb_primary_id, SUM(refund.amount) AS totalOfRefundAmount
                    FROM dealer_refunds AS refund
                    LEFT JOIN dealer_sales_receipt ON dealer_sales_receipt.tb_primary_id = refund.id
                    WHERE refund.tb_name='qb_payment' AND dealer_sales_receipt.tb_name = 'dealer_refunds' AND refund.dealer_id =" . $this->dealer->dealer_id .
                    " GROUP BY refund.tb_primary_id) as groupedRefund"),
                'groupedRefund.tb_primary_id',
                '=',
                'qb_payment.id'
            )
            ->leftJoin('crm_pos_register as register', 'register.id', '=', 'qb_payment.register_id')
            ->leftJoin('qb_invoices as i', 'i.id', '=', 'qb_payment.invoice_id')
            ->leftJoin('qb_invoice_items', 'qb_invoice_items.invoice_id', '=', 'qb_payment.invoice_id')
            ->leftJoin('qb_items', function ($join) {
                $join->on('qb_invoice_items.item_id', '=', 'qb_items.id')
                    ->where('qb_items.type', 'part');
            })
            ->leftJoin('parts_v1', 'qb_items.item_primary_id', '=', 'parts_v1.id')
            ->leftJoin('dms_customer as c', 'c.id', '=', 'i.customer_id')
            ->leftJoin('dealer_sales_receipt as sr', function ($join) {
                $join->on('sr.tb_primary_id', '=', 'qb_payment.id')
                    ->where('sr.tb_name', 'qb_payment');
            })
            ->leftJoin('qb_payment_methods as pm', 'pm.id', '=', 'qb_payment.payment_method_id')
            ->where('qb_payment.dealer_id', $this->dealer->dealer_id)
            ->groupBy('qb_payment.id');
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'payment_id' => 'Payment Identifier',
                'reference_number' => 'Reference Number',
                'invoice_id' => 'Invoice Identifier',
                'customer_id' => 'Customer Identifier',
                'display_name' => 'Customer Name',
                'sales_person_id' => 'Sales Person Identifier',
                'sales_person' => 'Sales Person',
                'sale_total' => 'Sales Total',
                'totalOfRefundAmount' => 'Refund Amount',
                'date' => 'Date',
                'payment_method_id' => 'Payment Method Identifier',
                'payment_method_name' => 'Payment Method',
                'receipt_path' => 'Receipt',
                'part_id' => 'Part Identifier',
                'parts_title' => 'Parts Title',
                'parts_sku' => 'Parts SKU',
                'parts_price' => 'Parts Prices',
                'parts_total' => 'Parts Total',
                'parts_qty' => 'Parts Qty',
                'parts_tax' => 'Parts Tax',
                'sales_total_tax' => 'Sales Total Tax',
                'sales_subtotal' => 'Sales Subtotal',
                'type' => 'Sales Type',
            ])
            ->export();
    }
}

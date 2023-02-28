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
        return DB::table('qb_payment')
            ->selectRaw("qb_payment.id, qb_payment.amount AS total, qb_payment.created_at AS date, c.display_name, c.id AS customer_id, sr.receipt_path,
            pm.name AS payment_method_name, pm.type AS payment_method_type, COALESCE(groupedRefund.refund_amount, 0) AS refund_amount,
            qb_payment.related_payment_intent AS payment_intent, i.id as invoice_id, qb_payment.id AS paymentId, '' AS salesPerson,
            i.total as saleTotal, i.doc_num AS reference_number, GROUP_CONCAT(parts_v1.sku) as parts_sku, GROUP_CONCAT(parts_v1.title) as parts_title")
            ->leftJoin(
                DB::raw("(SELECT refund.tb_primary_id, SUM(refund.amount) AS refund_amount
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
                'customer_id' => 'Customer Identifier',
                'customer_name' => 'Customer Name',
                'sales_total' => 'Sales Total',
                'refund_amount' => 'Refund Amount',
                'date' => 'Date',
                'paymen_method' => 'Payment Method',
                'reference_number' => 'Reference Number',
                'receipt_path' => 'Receipt',
                'parts_sku' => 'Parts SKU',
                'parts_title' => 'Parts Title',
            ])
            ->export();
    }
}

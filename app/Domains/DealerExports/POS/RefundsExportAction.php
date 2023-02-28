<?php

namespace App\Domains\DealerExports\POS;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use Illuminate\Support\Facades\DB;

/**
 * Class BillsExportAction
 *
 * @package App\Domains\DealerExports\BackOffice
 */
class RefundsExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'refunds';

    public function getQuery()
    {
        return DB::table('dealer_refunds')
            ->selectRaw(
                'dealer_refunds.id, dealer_refunds.amount, dealer_refunds.created_at, invoice.doc_num, invoice.invoice_date, sr.receipt_path'
            )
            ->leftJoin('qb_payment', 'qb_payment.id', '=', 'dealer_refunds.tb_primary_id')
            ->leftJoin('qb_invoices as invoice', 'invoice.id', '=', 'qb_payment.invoice_id')
            ->leftJoin('dealer_sales_receipt as sr', function ($join) {
                $join->on('sr.tb_primary_id', '=', 'dealer_refunds.tb_primary_id')
                ->where('sr.tb_name', 'qb_payment');
            })
            ->where('dealer_refunds.dealer_id', $this->dealer->dealer_id);
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'amount' => 'Amount',
                'created_at' => 'Created Date',
                'doc_num' => 'Document #',
                'invoice_date' => 'Invoice Date',
                'receipt_path' => 'Receipt',
            ])
            ->export();
    }
}

<?php

namespace App\Domains\DealerExports\BackOffice;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\CRM\Dms\Quickbooks\Bill;
use Illuminate\Support\Facades\DB;

/**
 * Class BillsExportAction
 *
 * @package App\Domains\DealerExports\BackOffice
 */
class BillsExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'bills';

    public function getQuery()
    {
        return Bill::query()
            ->where('qb_bills.dealer_id', $this->dealer->dealer_id)
            ->selectRaw('qb_bills.*,qb_vendors.name as vendor_name, bill_payments.paid as amount_paid, (qb_bills.total - bill_payments.paid) as remaining_balance')
            ->leftJoin('qb_vendors', 'qb_bills.vendor_id', '=', 'qb_vendors.id')
            ->leftJoin(
                DB::raw('(SELECT sum(amount) as paid, bill_id FROM qb_bill_payment GROUP BY bill_id) as bill_payments'),
                'bill_payments.bill_id',
                '=',
                'qb_bills.id'
            );
    }

    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'vendor_id' => 'Vendor Identifier',
                'vendor_name' => 'Vendor Name',
                'doc_num' => 'Bill Number',
                'status' => 'Status',
                'received_date' => 'Bill Date',
                'due_date' => 'Due Date',
                'total' => 'Total Amount',
                'amount_paid' => 'Amount Paid',
                'remaining_balance' => 'Remaining Balance',
            ])
            ->export();
    }
}

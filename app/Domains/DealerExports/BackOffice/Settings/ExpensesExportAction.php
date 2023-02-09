<?php

namespace App\Domains\DealerExports\BackOffice\Settings;

use App\Contracts\DealerExports\EntityActionExportable;
use App\Domains\DealerExports\BaseExportAction;
use App\Models\CRM\Dms\Quickbooks\Expense;

/**
 * Class ExpensesExportAction
 *
 * @package App\Domains\DealerExports\BackOffice\Settings
 */
class ExpensesExportAction extends BaseExportAction implements EntityActionExportable
{
    public const ENTITY_TYPE = 'expenses';

    public function getQuery()
    {
        return Expense::query()
            ->selectRaw('qb_expenses.*, qb_payment_methods.name AS payment_method, qb_accounts.name AS account, qb_invoices.doc_num AS invoice_num, dms_repair_order.user_defined_id AS repair_order_num')
            ->leftJoin('qb_payment_methods', 'qb_payment_methods.id', '=', 'qb_expenses.payment_method_id')
            ->leftJoin('qb_accounts', 'qb_accounts.id', '=', 'qb_expenses.account_id')
            ->leftJoin('qb_invoices', function ($query) {
                $query->on('qb_invoices.id', '=', 'qb_expenses.tb_primary_id')
                    ->where('qb_expenses.tb_name', 'qb_invoices');
            })
            ->leftJoin('dms_repair_order', function ($query) {
                $query->on('dms_repair_order.id', '=', 'qb_expenses.tb_primary_id')
                    ->where('qb_expenses.tb_name', 'dms_repair_order');
            })
            ->where('qb_expenses.dealer_id', $this->dealer->dealer_id);
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->setEntity(self::ENTITY_TYPE)
            ->setHeaders([
                'account' => 'Account',
                'doc_num' => 'Doc Num',
                'txn_date' => 'Transaction Date',
                'payment_method' => 'Payment Method',
            ])
            ->export();
    }
}

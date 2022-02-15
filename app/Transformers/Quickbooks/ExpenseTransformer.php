<?php


namespace App\Transformers\Quickbooks;


use App\Models\CRM\Dms\Quickbooks\Expense;
use League\Fractal\TransformerAbstract;

class ExpenseTransformer extends TransformerAbstract
{
    public function transform(Expense $expense): array
    {
        return [
            'id' => (int) $expense->id,
            'account' => $expense->account,
            'payment_method' => $expense->paymentMethod,
            'txn_date' => $expense->txn_date,
            'doc_num' => $expense->doc_num,
            'private_note' => $expense->private_note,
            'entity_type' => $expense->entity_type,
            'entity_id' => $expense->entity_id,
            'tb_name' => $expense->tb_name,
            'tb_primary_id' => $expense->tb_primary_id,
            'total_amount' => $expense->total_amount,
            'qb_id' => $expense->qb_id,
        ];
    }
}

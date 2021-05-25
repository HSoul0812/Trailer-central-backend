<?php

namespace App\Transformers\Dms\Quickbooks;

use League\Fractal\TransformerAbstract;

class QuickbookApprovalTransformer extends TransformerAbstract
{

    public function transform($quickbookApproval)
    {
        return [
            'id' => $quickbookApproval->id,
            'dealer_id' => $quickbookApproval->dealer_id,
            'qb_obj' => $quickbookApproval->qb_obj,
            'error_result' => $quickbookApproval->error_result,
            'tb_name' => $quickbookApproval->tb_name,
            'tb_primary_id' => $quickbookApproval->tb_primary_id,
            'tb_label' => $quickbookApproval->tb_label,
            'action_type' => $quickbookApproval->action_type,
            'created_at' => $quickbookApproval->created_at,
            'customer_name' => $quickbookApproval->customer_name,
            'payment_method' => $quickbookApproval->payment_method,
            'sales_ticket_num' => $quickbookApproval->sales_ticket_num,
            'ticket_total' => $quickbookApproval->ticket_total,
            'qbo_account' => $quickbookApproval->account,
            'removed_by' => $quickbookApproval->removed_by,
            'deleted_at' => $quickbookApproval->deleted_at
        ];
    }
}

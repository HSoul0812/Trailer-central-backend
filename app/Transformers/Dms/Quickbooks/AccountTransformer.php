<?php

namespace App\Transformers\Dms\Quickbooks;

use League\Fractal\TransformerAbstract;

class AccountTransformer extends TransformerAbstract
{

    public function transform($account)
    {   
        return [
            'id' => $account->id,
            'dealer_id' => $account->dealer_id,
            'name' => $account->name,
            'type' => $account->type,
            'sub_type' => $account->sub_type,
            'current_balance' => $account->current_balance,
            'sub_account' => (bool) $account->sub_account,
            'parent' => $account->parent,
            'qb_id' => $account->qb_id,
        ];
    }
} 
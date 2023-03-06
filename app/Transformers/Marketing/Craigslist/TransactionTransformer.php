<?php

namespace App\Transformers\Marketing\Craigslist;

use App\Models\Marketing\Craigslist\Transaction;
use League\Fractal\TransformerAbstract;

/**
 * Class TransactionTransformer
 * 
 * @package App\Transformers\Marketing\Craigslist
 */
class TransactionTransformer extends TransformerAbstract
{
    /**
     * @param Transaction $transaction
     * @return array
     */
    public function transform(Transaction $transaction): array
    {
        return [
            'id' => $transaction->clapp_txn_id,
            'dealer_id' => $transaction->dealer_id,
            'session_id' => $transaction->session_id,
            'queue_id' => $transaction->queue_id,
            'inventory_id' => $transaction->inventory_id,
            'ip_addr' => $transaction->ip_addr,
            'user_agent' => $transaction->user_agent,
            'amount' => $transaction->amount,
            'balance' => $transaction->balance,
            'post' => $transaction->post,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at
        ];
    }
}
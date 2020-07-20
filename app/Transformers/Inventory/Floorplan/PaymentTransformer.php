<?php

namespace App\Transformers\Inventory\Floorplan;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\Floorplan\Payment;

class PaymentTransformer extends TransformerAbstract
{
    public function transform(Payment $payment)
    {
	    return [
             'id' => (int) $payment->id,
             'inventory' => $payment->inventory,
             'bank_account' => $payment->account,
             'type' => $payment->type,
             'amount' => (double) $payment->amount,
             'payment_type' => $payment->payment_type,
             'check_number' => $payment->check_number,
             'created_at' => $payment->created_at,
             'qb_id' => $payment->qb_id,
        ];
    }
}
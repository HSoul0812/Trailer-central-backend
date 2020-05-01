<?php

namespace App\Transformers\Inventory;

use League\Fractal\TransformerAbstract;
use App\Models\Inventory\FloorplanPayment;

class FloorplanPaymentTransformer extends TransformerAbstract
{
    public function transform(FloorplanPayment $floorplanPayment)
    {
	    return [
             'id' => (int) $floorplanPayment->id,
             'inventory' => $floorplanPayment->inventory,
             'type' => $floorplanPayment->type,
             'amount' => (double) $floorplanPayment->amount,
             'payment_type' => $floorplanPayment->payment_type,
             'check_number' => $floorplanPayment->check_number,
             'created_at' => $floorplanPayment->created_at
        ];
    }
}
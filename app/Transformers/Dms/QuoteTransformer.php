<?php

namespace App\Transformers\Dms;

use League\Fractal\TransformerAbstract;

class QuoteTransformer extends TransformerAbstract
{

    public function transform($quote)
    {
        if (isset($quote->quote)) {
            $quote = $quote->quote;
        }

        return [
            'id' => $quote->id,
            'dealer_id' => $quote->dealer_id,
            'title' => $quote->title,
            'customer' => $quote->customer,
            'created_at' => $quote->created_at,
            'total_price' => $quote->total_price,
            'invoice' => $quote->invoice,
            'paid_amount' => (float) $quote->paid_amount,
            'inventory_id' => $quote->inventory_id,
            'inventory_vin' => $quote->inventory_vin,
            'status' => $quote->status,
            'location' => json_decode(json_encode($quote->location, JSON_PARTIAL_OUTPUT_ON_ERROR)),
            'completed_at' => $quote->completed_at,
        ];
    }
}

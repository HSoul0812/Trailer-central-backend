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
            'status' => $quote->status,
        ];
    }
} 
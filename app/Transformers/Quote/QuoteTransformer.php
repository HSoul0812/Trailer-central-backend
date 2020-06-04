<?php

namespace App\Transformers\Quote;

use League\Fractal\TransformerAbstract;
use App\Models\Quote\Quote;

class QuoteTransformer extends TransformerAbstract
{
    public function transform($quote)
    {   
        if (isset($quote->quote)) {
            $quote = $quote->quote;
        }
        
        return [
            'id' => (int) $quote->id,
            'title' => $quote->title,
            'customer' => $quote->customer,
            'created_at' => $quote->created_at,
            'total_price' => $quote->total_price,
        ];
    }
} 
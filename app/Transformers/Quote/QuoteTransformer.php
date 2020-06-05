<?php

namespace App\Transformers\Quote;

use League\Fractal\TransformerAbstract;
use App\Models\Quote\Quote;

class QuoteTransformer extends TransformerAbstract
{

    private function getQuoteStatus(Quote $quote) {
        if (!empty($quote->is_archived)) {
            return 'Archived';
        }
        if (empty($quote->paid_amount)) {
            return 'Open';
        }

        $balance = (float) $quote->total_price - (float) $quote->paid_amount;
        if ($balance > 0) {
            return 'Deal';
        }
        return 'Completed Deal';
    }

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
            'status' => $this->getQuoteStatus($quote),
        ];
    }
} 
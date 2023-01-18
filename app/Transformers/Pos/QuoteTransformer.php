<?php


namespace App\Transformers\Pos;


use App\Models\Pos\Quote;
use League\Fractal\TransformerAbstract;

class QuoteTransformer extends TransformerAbstract
{
    public function transform(Quote $quote)
    {
        return [
            'id' => (int)$quote->id,
            'quote_details' => $quote->quote_details,
            'created_at' => $quote->created_at,
            'updated_at' => $quote->updated_at,
        ];
    }
}

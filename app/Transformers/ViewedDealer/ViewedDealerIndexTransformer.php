<?php

namespace App\Transformers\ViewedDealer;

use App\Models\Dealer\ViewedDealer;
use League\Fractal\TransformerAbstract;

class ViewedDealerIndexTransformer extends TransformerAbstract
{
    public function transform(ViewedDealer $viewedDealer): array
    {
        return [
            'id' => $viewedDealer->id,
            'dealer_id' => $viewedDealer->dealer_id,
            'name' => $viewedDealer->name,
            'created_at' => $viewedDealer->created_at,
            'updated_at' => $viewedDealer->updated_at,
        ];
    }
}

<?php

namespace App\Transformers\ViewedDealer;

use App\Models\Dealer\ViewedDealer;
use League\Fractal\TransformerAbstract;

class ViewedDealerCreateTransformer extends TransformerAbstract
{
    public function transform(ViewedDealer $viewedDealer): array
    {
        return [
            'id' => $viewedDealer->id,
            'name' => $viewedDealer->name,
            'dealer_id' => $viewedDealer->dealer_id,
            'inventory_id' => $viewedDealer->inventory_id,
            'created_at' => $viewedDealer->created_at,
            'updated_at' => $viewedDealer->updated_at,
        ];
    }
}

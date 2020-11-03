<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Catalog;
use App\Transformers\Integration\Auth\TokenTransformer;

class CatalogTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'token'
    ];

    public function transform(Catalog $catalog)
    {
        return [
            'id' => $catalog->id,
            'dealer_id' => $catalog->dealer_id,
            'dealer_location_id' => $catalog->dealer_location_id,
            'account_name' => $catalog->account_name,
            'user_id' => $catalog->user_id,
            'filters' => $catalog->filters,
            'created_at' => $catalog->created_at,
            'updated_at' => $catalog->updated_at
        ];
    }

    public function includeToken(SalesPerson $salesPerson)
    {
        return $this->item($salesPerson->access_token, new TokenTransformer());
    }
}

<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Catalog;
use App\Transformers\Integration\Auth\TokenTransformer;

class CatalogTransformer extends TransformerAbstract
{
    protected $availableIncludes = [
        'accessToken'
    ];

    public function transform(Catalog $catalog)
    {
        return [
            'id' => $catalog->id,
            'dealer_id' => $catalog->dealer_id,
            'account_id' => $catalog->account_id,
            'account_name' => $catalog->account_name,
            'page_id' => $catalog->page_id,
            'page_title' => $catalog->page_title,
            'filters' => json_decode($catalog->filters),
            'is_active' => (boolean) $catalog->is_active,
            'is_scheduled' => (boolean) $catalog->is_scheduled,
            'created_at' => $catalog->created_at,
            'updated_at' => $catalog->updated_at
        ];
    }

    public function includeAccessToken(Catalog $catalog)
    {
        return $this->item($catalog->accessToken, new TokenTransformer());
    }
}

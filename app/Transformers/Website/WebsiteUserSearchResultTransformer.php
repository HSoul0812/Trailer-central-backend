<?php

namespace App\Transformers\Website;

use App\Models\Website\User\WebsiteUser;
use App\Models\Website\User\WebsiteUserFavoriteInventory;
use App\Models\Website\User\WebsiteUserSearchResult;
use App\Models\Website\Website;
use App\Transformers\Inventory\InventoryTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class WebsiteTransformer
 * @package App\Transformers\Website
 */
class WebsiteUserSearchResultTransformer extends TransformerAbstract
{
    public function transform(WebsiteUserSearchResult $searchResult): array
    {
        return [
            'id' => $searchResult->id,
            'website_user_id' => $searchResult->website_user_id,
            'search_url' => $searchResult->search_url,
            'summary' => $searchResult->summary,
            'created_at' => $searchResult->created_at,
         ];
    }
}

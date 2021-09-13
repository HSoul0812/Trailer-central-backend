<?php

namespace App\Transformers\Integration\Facebook;

use League\Fractal\TransformerAbstract;
use App\Models\Integration\Facebook\Catalog;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use App\Transformers\Integration\Facebook\PageTransformer;
use App\Transformers\Integration\Auth\TokenTransformer;

class CatalogTransformer extends TransformerAbstract
{
    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var DealerLocationTransformer
     */
    protected $dealerLocationTransformer;

    /**
     * @var PageTransformer
     */
    protected $pageTransformer;

    /**
     * @var CatalogTransformer
     */
    protected $tokenTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
        $this->dealerLocationTransformer = new DealerLocationTransformer();
        $this->pageTransformer = new PageTransformer();
        $this->tokenTransformer = new TokenTransformer();
    }

    public function transform(Catalog $catalog)
    {
        return [
            'id' => $catalog->id,
            'dealer' => $this->userTransformer->transform($catalog->user),
            'dealer_location' => $this->dealerLocationTransformer->transform($catalog->dealerLocation),
            'business_id' => $catalog->business_id,
            'catalog_id' => $catalog->catalog_id,
            'catalog_name' => $catalog->catalog_name_id,
            'catalog_type' => $catalog->catalog_type,
            'account_id' => $catalog->account_id,
            'account_name' => $catalog->account_name,
            'access_token' => $this->tokenTransformer->transform($catalog->accessToken),
            'page' => $this->pageTransformer->transform($catalog->page),
            'feed_name' => $catalog->feed_name,
            'feed_path' => $catalog->feed_path,
            'feed_url' => $catalog->feed_url,
            'feed_id' => $catalog->feed_id,
            'filters' => json_decode($catalog->filters),
            'is_active' => (boolean) $catalog->is_active,
            'created_at' => $catalog->created_at,
            'updated_at' => $catalog->updated_at
        ];
    }
}

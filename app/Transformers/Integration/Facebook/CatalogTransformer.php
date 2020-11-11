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
    protected $defaultIncludes = [
        'accessToken',
        'page'
    ];

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var DealerLocationTransformer
     */
    protected $dealerLocationTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserTransformer();
        $this->dealerLocationTransformer = new DealerLocationTransformer();
    }

    public function transform(Catalog $catalog)
    {
        return [
            'id' => $catalog->id,
            'dealer' => $this->userTransformer->transform($catalog->user),
            'dealer_location' => $this->dealerLocationTransformer->transform($catalog->dealerLocation),
            'account_id' => $catalog->account_id,
            'account_name' => $catalog->account_name,
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

    public function includePage(Catalog $catalog)
    {
        return $this->item($catalog->page, new PageTransformer());
    }

    public function includeAccessToken(Catalog $catalog)
    {
        // Access Token Exists on Catalog?
        if(!empty($catalog->accessToken)) {
            return $this->item($catalog->accessToken, new TokenTransformer());
        }
        return $this->item(null, function() {
            return [null];
        });
    }
}

<?php

namespace App\Transformers\Marketing\Facebook;

use App\Models\Marketing\Facebook\Marketplace;
use App\Transformers\Marketing\Facebook\FilterTransformer;
use App\Transformers\User\UserTransformer;
use App\Transformers\User\DealerLocationTransformer;
use League\Fractal\TransformerAbstract;

class MarketplaceTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'filters',
    ];

    /**
     * @var FilterTransformer
     */
    protected $filterTransformer;

    /**
     * @var UserTransformer
     */
    protected $userTransformer;

    /**
     * @var DealerLocationTransformer
     */
    protected $dealerLocationTransformer;

    public function __construct(
        FilterTransformer $filterTransformer,
        UserTransformer $userTransformer,
        DealerLocationTransformer $dealerLocationTransformer
    ) {
        $this->filterTransformer = $filterTransformer;
        $this->userTransformer = $userTransformer;
        $this->dealerLocationTransformer = $dealerLocationTransformer;
    }

    public function transform(Marketplace $marketplace)
    {
        return [
            'id' => $marketplace->id,
            'dealer' => $this->userTransformer->transform($marketplace->user),
            'dealer_location' => $this->dealerLocationTransformer->transform($marketplace->dealerLocation),
            'page_url' => $marketplace->page_url,
            'fb_username' => $marketplace->fb_username,
            'fb_password' => $marketplace->fb_password,
            'tfa_username' => $marketplace->tfa_username,
            'tfa_password' => $marketplace->tfa_password,
            'tfa_type' => $marketplace->tfa_type,
            'filter_map' => $marketplace->filter_map,
            'created_at' => $marketplace->created_at,
            'updated_at' => $marketplace->updated_at
        ];
    }

    public function includeFilters(Marketplace $marketplace)
    {
        return $this->collect($marketplace->filters, $this->filterTransformer);
    }
}

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

    public function __construct()
    {
        $this->filterTransformer = new FilterTransformer;
        $this->userTransformer = new UserTransformer;
        $this->dealerLocationTransformer = new DealerLocationTransformer;
    }

    public function transform(Marketplace $marketplace)
    {
        return [
            'id' => $marketplace->id,
            'dealer' => $this->userTransformer->transform($marketplace->user),
            'dealer_location' => !empty($marketplace->dealerLocation) ? $this->dealerLocationTransformer->transform($marketplace->dealerLocation) : null,
            'page_url' => $marketplace->page_url,
            'fb_username' => $marketplace->fb_username,
            'fb_password' => $marketplace->fb_password,
            'tfa_username' => $marketplace->tfa_username,
            'tfa_password' => $marketplace->tfa_password,
            'tfa_code' => $marketplace->tfa_code,
            'tfa_type' => $marketplace->tfa_type,
            'filter_map' => $marketplace->filter_map,
            'is_up_to_date' => $marketplace->is_up_to_date,
            'imported_at' => $marketplace->imported_at,
            'created_at' => $marketplace->created_at,
            'updated_at' => $marketplace->updated_at
        ];
    }

    public function includeFilters(Marketplace $marketplace)
    {
        return $this->collection($marketplace->filters, $this->filterTransformer);
    }
}

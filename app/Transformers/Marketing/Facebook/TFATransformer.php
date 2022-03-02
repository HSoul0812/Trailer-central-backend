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
        'filters'
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

    public function transform(TfaType $type)
    {
        // Return Array
        return [
            'code' => $type->code,
            'name' => $type->name,
            'fields' => $type->getFields(),
            'autocomplete' => $type->getAutocomplete(),
            'note' => $type->getNote()
        ];
    }
}

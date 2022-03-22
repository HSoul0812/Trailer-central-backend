<?php

namespace App\Transformers\Marketing\Facebook;

use App\Services\Marketing\Facebook\DTOs\MarketplaceStatus;
use App\Transformers\Marketing\Facebook\TFATransformer;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'tfaTypes',
    ];

    /**
     * @var TFATransformer
     */
    protected $tfaTransformer;

    public function __construct(
        TFATransformer $tfaTransformer
    ) {
        $this->tfaTransformer = $tfaTransformer;
    }

    public function transform(MarketplaceStatus $status)
    {
        // Return Array
        return [
            'page_url' => $status->pageUrl
        ];
    }

    public function includeTfaTypes(MarketplaceStatus $status)
    {
        return $this->collection($status->tfaTypes, $this->tfaTransformer);
    }
}

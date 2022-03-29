<?php

namespace App\Transformers\Marketing\Facebook;

use App\Services\Marketing\Facebook\DTOs\MarketplaceStatus;
use App\Transformers\Marketing\Facebook\ErrorTransformer;
use App\Transformers\Marketing\Facebook\TFATransformer;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    protected $defaultIncludes = [
        'errors',
        'tfaTypes',
    ];

    /**
     * @var ErrorTransformer
     */
    protected $errorTransformer;

    /**
     * @var TFATransformer
     */
    protected $tfaTransformer;

    public function __construct(
        ErrorTransformer $errorTransformer,
        TFATransformer $tfaTransformer
    ) {
        $this->errorTransformer = $errorTransformer;
        $this->tfaTransformer = $tfaTransformer;
    }

    public function transform(MarketplaceStatus $status)
    {
        // Return Array
        return [
            'page_url' => $status->pageUrl
        ];
    }

    public function includeErrors(MarketplaceStatus $status)
    {
        return $this->collection($status->errors, $this->errorTransformer);
    }

    public function includeTfaTypes(MarketplaceStatus $status)
    {
        return $this->collection($status->tfaTypes, $this->tfaTransformer);
    }
}

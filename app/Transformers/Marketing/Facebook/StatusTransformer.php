<?php

namespace App\Transformers\Marketing\Facebook;

use App\Services\Marketing\Facebook\DTOs\MarketplaceStatus;
use League\Fractal\TransformerAbstract;

class StatusTransformer extends TransformerAbstract
{
    public function transform(MarketplaceStatus $status)
    {
        // Return Array
        return [
            ''
        ];
    }
}

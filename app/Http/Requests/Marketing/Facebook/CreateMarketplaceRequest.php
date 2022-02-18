<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Marketing\Facebook\SaveMarketplaceRequest;

/**
 * Create Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class CreateMarketplaceRequest extends SaveMarketplaceRequest {
    protected function getRules(): array
    {
        return array_merge([
            'dealer_id' => 'required|integer',
            'dealer_location_id' => 'required|integer',
        ], parent::getRules());
    }
}
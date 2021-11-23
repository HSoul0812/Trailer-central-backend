<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Marketing\Facebook\SaveMarketplaceRequest;

/**
 * Update Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class UpdateMarketplaceRequest extends SaveMarketplaceRequest {
    protected function getRules(): array
    {
        return array_merge([
            'id' => 'required|integer'
        ], parent::getRules());
    }
}
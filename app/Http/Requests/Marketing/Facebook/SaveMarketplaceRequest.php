<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Filter;

/**
 * Class SaveMarketplaceRequest
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class SaveMarketplaceRequest extends Request
{
    protected function getRules(): array
    {
        // Get Rules
        return [
            'dealer_location_id' => 'nullable|dealer_location_valid',
            'page_url' => 'string',
            'fb_username' => 'required|string',
            'fb_password' => 'required|string',
            'tfa_username' => 'nullable|string',
            'tfa_password' => 'nullable|string',
            'tfa_type' => 'in:' . implode(",", array_keys(Marketplace::TFA_TYPES)),
            'filter.*' => 'array',
            'filter.*.type' => 'in:' . implode(",", array_keys(Filter::FILTER_TYPES)),
            'filter.*.value' => 'string'
        ];
    }
}
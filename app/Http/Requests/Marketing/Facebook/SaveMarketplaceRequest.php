<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;
use App\Models\Marketing\Facebook\Marketplace;
use App\Models\Marketing\Facebook\Filter;

/**
 * Class SaveInventoryRequest
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class SaveInventoryRequest extends Request
{
    public function getRules(): array
    {
        // Get Rules
        return [
            'dealer_location_id' => 'required|integer',
            'page_url' => 'string',
            'fb_username' => 'required|string',
            'fb_password' => 'required|string',
            'tfa_username' => 'string',
            'tfa_password' => 'string',
            'tfa_type' => 'in:' . implode(",", array_keys(Marketplace::TFA_TYPES)),
            'filter.*' => 'array',
            'filter.*.type' => 'in:' . implode(",", array_keys(Filter::FILTER_TYPES)),
            'filter.*.value' => 'string'
        ];
    }
}
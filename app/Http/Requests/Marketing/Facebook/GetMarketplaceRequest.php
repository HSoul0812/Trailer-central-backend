<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Facebook Marketplace Request
 * 
 * @package App\Http\Requests\Marketing\Facebook
 * @author David A Conway Jr.
 */
class GetMarketplaceRequest extends Request {
    protected $rules = [
        'dealer_id' => 'required|integer',
        'dealer_location_id' => 'integer',
        'per_page' => 'integer',
        'sort' => 'in:location,-location,created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
}
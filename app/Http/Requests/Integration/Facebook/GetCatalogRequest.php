<?php

namespace App\Http\Requests\Integration\Facebook;

use App\Http\Requests\Request;

/**
 * Get Facebook Catalog Request
 * 
 * @author David A Conway Jr.
 */
class GetCatalogRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer',
        'dealer_location_id' => 'integer',
        'user_id' => 'integer',
        'per_page' => 'integer',
        'sort' => 'in:account_name,-account_name,page_title,-page_title,created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
}

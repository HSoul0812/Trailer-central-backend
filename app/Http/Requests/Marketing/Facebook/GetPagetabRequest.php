<?php

namespace App\Http\Requests\Marketing\Facebook;

use App\Http\Requests\Request;

/**
 * Get Facebook Page Tab Request
 * 
 * @author David A Conway Jr.
 */
class GetPagetabRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer',
        'page_id' => 'integer',
        'per_page' => 'integer',
        'sort' => 'in:created_at,-created_at,updated_at,-updated_at',
        'id' => 'array',
        'id.*' => 'integer'
    ];
}

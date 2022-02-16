<?php

namespace App\Http\Requests\Marketing\Craigslist;

use App\Http\Requests\Request;

/**
 * Get Active Posts Request
 * 
 * @author David A Conway Jr.
 */
class GetActivePostsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|integer',
        'profile_id' => 'integer|valid_clapp_profile',
        'slot_id' => 'integer',
        'sort' => 'in:scheduled,-scheduled,added,-added,updated,-updated',
        'per_page' => 'integer',
        'page' => 'integer'
    ];
}

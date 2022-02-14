<?php

namespace App\Http\Requests\Marketing\Craigslist;

use App\Http\Requests\Request;

/**
 * Get Profile Request
 */
class GetProfileRequest extends Request {
    
    protected $rules = [
        'type' => 'in:inventory',
        'sort' => 'in:username,-username',
        'dealer_id' => 'integer'
    ];
}

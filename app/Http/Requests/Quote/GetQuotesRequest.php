<?php

namespace App\Http\Requests\Quote;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetQuotesRequest extends Request {

    protected $rules = [
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
        'status' => 'string'
    ];
    
}

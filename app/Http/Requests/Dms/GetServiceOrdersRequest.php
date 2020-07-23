<?php

namespace App\Http\Requests\Dms;

use App\Http\Requests\Request;

/**
 * @author Marcel
 */
class GetServiceOrdersRequest extends Request {

    protected $rules = [
        'dealer_id' => 'integer',
        'status' => 'string'
    ];
    
}
<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *  
 * @author Eczek
 */
class GetVendorsRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'array',
        'dealer_id.*' => 'integer',
    ];
    
}

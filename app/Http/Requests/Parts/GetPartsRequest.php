<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *  
 * @author Eczek
 */
class GetPartsRequest extends Request {
    
    protected $rules = [
        'page_size' => 'integer'
    ];
    
}

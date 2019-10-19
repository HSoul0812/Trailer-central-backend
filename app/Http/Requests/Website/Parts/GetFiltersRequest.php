<?php

namespace App\Http\Requests\Website\Parts;

use App\Http\Requests\Request;

/**
 * 
 *
 * @author Eczek
 */
class GetFiltersRequest extends Request {
    
    protected $rules = [
        'dealer_id' => 'required|array',
        'dealer_id.*' => 'integer'
    ];
    
}

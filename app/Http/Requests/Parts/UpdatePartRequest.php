<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 *  
 * @author Eczek
 */
class UpdatePartRequest extends Request {
    
    protected $rules = [
        'id' => 'required'
    ];
    
}

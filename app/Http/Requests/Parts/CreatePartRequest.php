<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * 
 *
 * @author Eczek
 */
class CreatePartRequest extends Request {
    
    protected $rules = [
        'weight_rating' => 'required'
    ];
    
}

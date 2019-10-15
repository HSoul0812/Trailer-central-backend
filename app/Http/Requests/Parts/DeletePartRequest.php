<?php

namespace App\Http\Requests\Parts;

use App\Http\Requests\Request;

/**
 * 
 *
 * @author Eczek
 */
class DeletePartRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}

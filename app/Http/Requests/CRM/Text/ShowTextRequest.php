<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Show Text Request
 * 
 * @author David A Conway Jr.
 */
class ShowTextRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}

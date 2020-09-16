<?php

namespace App\Http\Requests\CRM\Interactions;

use App\Http\Requests\Request;

/**
 * Show Interaction Request
 * 
 * @author David A Conway Jr.
 */
class ShowInteractionRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}

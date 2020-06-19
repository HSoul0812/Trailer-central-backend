<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Show Text Request
 * 
 * @author David A Conway Jr.
 */
class StopTextRequest extends Request {
    
    protected $rules = [
        'response_id' => 'nullable|integer',
        'text_number' => 'required|string'
    ];
    
}

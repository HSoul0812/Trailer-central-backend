<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Show Template Request
 * 
 * @author David A Conway Jr.
 */
class ShowTemplateRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}

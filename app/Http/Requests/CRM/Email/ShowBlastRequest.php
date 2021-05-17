<?php

namespace App\Http\Requests\CRM\Email;

use App\Http\Requests\Request;

/**
 * Show Blast Request
 * 
 * @author David A Conway Jr.
 */
class ShowBlastRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer'
    ];
    
}

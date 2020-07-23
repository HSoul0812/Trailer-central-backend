<?php

namespace App\Http\Requests\CRM\Text;

use App\Http\Requests\Request;

/**
 * Update Text Request
 * 
 * @author David A Conway Jr.
 */
class UpdateTextRequest extends Request {
    
    protected $rules = [
        'id' => 'required|integer',
        'message' => 'string',
        'from_number' => 'string',
        'to_number' => 'string',
        'date_sent' => 'string'
    ];
    
}
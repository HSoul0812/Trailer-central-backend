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
        'log_message' => 'string|max:160',
        'from_number' => 'regex:/(0-9)?[0-9]{10}/',
        'to_number' => 'regex:/(0-9)?[0-9]{10}/',
        'date_sent' => 'date_format:Y-m-d H:i:s'
    ];
    
}